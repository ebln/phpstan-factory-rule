<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Reports #[ForceFactory] attributes that the instantiation rule cannot act on, and classes
 * that still implement the marker interface of the removed ebln/phpstan-factory-mark package.
 *
 * @implements Rule<Node\Stmt\ClassLike>
 */
final class ForceFactoryDefinitionRule implements Rule
{
    private const LEGACY_INTERFACE = 'Ebln\PHPStan\EnforceFactory\ForceFactoryInterface';

    public function __construct(
        private readonly AllowedFactoriesResolver $resolver,
        private readonly ReflectionProvider $reflectionProvider,
    ) {}

    public function getNodeType(): string
    {
        return Node\Stmt\ClassLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        // a raw class-like statement, not InClassNode: PHPStan emits no InClassNode for a trait analysed on its own
        $errors = [];
        if ($this->implementsLegacyInterface($node, $scope)) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                '%s implements the removed %s.',
                isset($node->namespacedName) ? $node->namespacedName->toString() : 'Anonymous class',
                self::LEGACY_INTERFACE,
            ))
                ->identifier('ebln.forceFactory.legacyInterface')
                ->tip('Replace the interface with the #[ForceFactory] attribute.')
                ->build()
            ;
        }
        if (!isset($node->namespacedName)) {
            return $errors; // anonymous class: an attribute on it is ignored, so there is nothing to validate
        }

        $className = $node->namespacedName->toString();
        if (!$this->reflectionProvider->hasClass($className)) {
            return $errors; // a declaration the provider cannot reflect: nothing to validate
        }

        return array_merge($errors, $this->attributeErrors($this->reflectionProvider->getClass($className)));
    }

    /** @return list<IdentifierRuleError> */
    private function attributeErrors(ClassReflection $class): array
    {
        $declared = $this->resolver->declaredFactories($class);
        if (null === $declared) {
            return []; // no attribute: nothing to validate
        }

        $invalidTarget = $this->invalidTarget($class);
        if (null !== $invalidTarget) {
            return [
                RuleErrorBuilder::message(sprintf('#[ForceFactory] is not allowed on %s %s.', $invalidTarget, $class->getName()))
                    ->identifier('ebln.forceFactory.invalidTarget')
                    ->tip('Only classes can be instantiated; put the attribute on the class.')
                    ->build(),
            ];
        }

        if ([] === $declared) {
            return [
                RuleErrorBuilder::message(sprintf('#[ForceFactory] on %s lists no factory classes.', $class->getName()))
                    ->identifier('ebln.forceFactory.noFactories')
                    ->tip('Pass at least one class name constant, e.g. #[ForceFactory(MyFactory::class)].')
                    ->build(),
            ];
        }

        return array_merge($this->factoryErrors($class, $declared), $this->covarianceErrors($class));
    }

    /**
     * @param list<string> $declared
     *
     * @return list<IdentifierRuleError>
     */
    private function factoryErrors(ClassReflection $class, array $declared): array
    {
        $errors = [];
        foreach ($declared as $name) {
            $factory = $this->resolver->classNamed($name);
            if (null === $factory) {
                $errors[] = $this->unknownFactory($class, $name);

                continue;
            }
            $kind = $this->nonClassKind($factory);
            if (null !== $kind) {
                // no `new` ever runs in the scope of an interface or a trait, so such a name allows nothing
                $errors[] = RuleErrorBuilder::message(sprintf(
                    '#[ForceFactory] on %s names %s %s, which cannot be a factory.',
                    $class->getName(),
                    $kind,
                    $factory->getName(),
                ))
                    ->identifier('ebln.forceFactory.invalidFactory')
                    ->tip('List the classes that create the product; naming an interface or a trait allows nothing.')
                    ->build()
                ;
            }
        }

        return $errors;
    }

    /**
     * Factories narrow with the product: every factory of a marked subclass must be one of the
     * nearest marked ancestor's factories or a subclass of one. Unknown names are left to
     * unknownFactory; an ancestor without usable factories is left to its own noFactories.
     *
     * @return list<IdentifierRuleError>
     */
    private function covarianceErrors(ClassReflection $class): array
    {
        $ancestor = $this->resolver->markedAncestor($class);
        if (null === $ancestor) {
            return [];
        }
        $ancestorFactories = $this->resolver->allowedFactories($ancestor) ?? [];
        if ([] === $ancestorFactories) {
            return [];
        }

        $errors = [];
        foreach ($this->resolver->allowedFactories($class) ?? [] as $factory) {
            $reflection = $this->reflectionProvider->getClass($factory);
            if (null !== $this->nonClassKind($reflection)) {
                continue; // reported as invalidFactory: one cause, one error
            }
            if (!$this->resolver->isFactoryOrSubclass($reflection, $ancestorFactories)) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    '#[ForceFactory] on %s names %s, which is not a subclass of a factory of %s (%s).',
                    $class->getName(),
                    $factory,
                    $ancestor->getName(),
                    implode(' or ', $ancestorFactories),
                ))
                    ->identifier('ebln.forceFactory.factoryNotCovariant')
                    ->tip(sprintf(
                        'A subclass may only narrow the factories of its parent: list %s or a subclass of it.',
                        implode(' or ', $ancestorFactories),
                    ))
                    ->build()
                ;
            }
        }

        return $errors;
    }

    private function implementsLegacyInterface(Node\Stmt\ClassLike $classLike, Scope $scope): bool
    {
        $parents = match (true) {
            $classLike instanceof Node\Stmt\Class_     => $classLike->implements,
            $classLike instanceof Node\Stmt\Enum_      => $classLike->implements,
            $classLike instanceof Node\Stmt\Interface_ => $classLike->extends,
            default                                    => [],
        };
        foreach ($parents as $parent) {
            // resolved by name on purpose: once the mark package is gone the interface cannot be reflected
            if (0 === strcasecmp($scope->resolveName($parent), self::LEGACY_INTERFACE)) {
                return true;
            }
        }

        return false;
    }

    /** 'interface' or 'trait' when the listed factory cannot run `new` in its own scope, else null. */
    private function nonClassKind(ClassReflection $factory): ?string
    {
        return match (true) {
            $factory->isInterface() => 'interface',
            $factory->isTrait()     => 'trait',
            default                 => null,
        };
    }

    private function invalidTarget(ClassReflection $class): ?string
    {
        return match (true) {
            $class->isInterface() => 'interface',
            $class->isTrait()     => 'trait',
            $class->isEnum()      => 'enum',
            default               => null,
        };
    }

    private function unknownFactory(ClassReflection $class, string $name): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf('#[ForceFactory] on %s names unknown class %s.', $class->getName(), $name))
            ->identifier('ebln.forceFactory.unknownFactory')
            ->build()
        ;
    }
}
