<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use Ebln\Attrib\ForceFactory;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements \PHPStan\Rules\Rule<Node\Expr\New_>
 */
class ForceFactoryRule implements Rule
{
    private ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getNodeType(): string
    {
        return \PhpParser\Node\Expr\New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($this->getClassNames($node, $scope) as [$class, $isName]) {
            if (!$isName) {
                continue; // newly instantiated class name couldn't be infered
            }
            /** @var class-string $class → sadly Psalm cannot be convinced to return class-string for getClassNames in reasonable amount of time */
            $allowedFactories = $this->getAllowedFactories($class);
            if (null === $allowedFactories) {
                continue; // newly instantiated class doesn't implement ForceFactory interface
            }

            if ([] === $allowedFactories) {
                $errors[] = RuleErrorBuilder::message(
                    ltrim($class, '\\') . ' has either no factories defined or a conflict between interface and attribute!'
                )->identifier('ebln.forceFactory.bogusDefinition')
                    ->build()
                ;

                continue; // bogus configuration
            }

            /** @psalm-suppress PossiblyNullReference | sad that even phpstan cannot infer that from isInClass */
            if (
                $scope->isInClass()
                && null !== $scope->getClassReflection() /** @phpstan-ignore notIdentical.alwaysTrue */
                && in_array($scope->getClassReflection()->getName(), $allowedFactories, true)
            ) {
                continue; // happy case: ForceFactoryInterface got created within an allowed class
            }

            $errors[] = RuleErrorBuilder::message(
                ltrim($class, '\\') . ' must be instantiated by ' . implode(' or ', $allowedFactories) . '!'
            )->identifier('ebln.forceFactory.outOfFactoryInstanciation')
                ->tip('Only use ' . implode(' or ', $allowedFactories) . ' to create an instance of ' . ltrim($class, '\\') . '!')
                ->build()
            ;
        }

        return $errors;
    }

    /**
     * @phpstan-param class-string $className
     *
     * @return null|string[] List of FQCNs
     *
     * @phpstan-return null|class-string[]
     */
    private function getAllowedFactories(string $className): ?array
    {
        $allowedFactories = $this->getFactoriesFromAttribute($className);
        /** @phpstan-ignore-next-line phpstanApi.runtimeReflection (seems okay for now) */
        if (is_a($className, ForceFactoryInterface::class, true)) {
            /* phpstan-var class-string<ForceFactoryInterface> $className */
            $interfaceFactories = $className::getFactories();
            sort($interfaceFactories);
            if (null === $allowedFactories) {
                $allowedFactories = $interfaceFactories;
            } elseif ($allowedFactories !== $interfaceFactories) {
                $allowedFactories = []; // Will result in a bogus definition error
            }
        }

        return $allowedFactories;
    }

    /**
     * @phpstan-param class-string $className
     *
     * @return null|array<class-string>
     */
    private function getFactoriesFromAttribute(string $className): ?array
    {
        if (\PHP_VERSION_ID < 80000 && $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $reflection = $this->reflectionProvider->getClass($className);
        /* psalm-suppress UndefinedClass */
        $allowedFactories = [];
        do {
            /** @psalm-suppress UndefinedClass */
            $allowedFactories = [...$allowedFactories, ...$this->getFactoriesFromAttributeByClass($reflection->getNativeReflection())];
        } while ($reflection = $reflection->getParentClass());

        if (empty($allowedFactories)) {
            return null;
        }
        $allowedFactories = array_filter($allowedFactories);
        sort($allowedFactories);

        return $allowedFactories;
    }

    /**
     * @psalm-suppress UndefinedDocblockClass,MismatchingDocblockParamType
     *
     * @psalm-param \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass|\PHPStan\BetterReflection\Reflection\Adapter\ReflectionEnum $reflection
     *
     * @return array<int, null|class-string>
     */
    private function getFactoriesFromAttributeByClass(\ReflectionClass $reflection): array
    {
        /** @psalm-suppress UndefinedClass */
        foreach ($reflection->getAttributes() as $attribute) {
            if (ForceFactory::class === $attribute->getName()) {
                /** @var ForceFactory $forceFactory */
                $forceFactory     = $attribute->newInstance();
                $allowedFactories = $forceFactory->getAllowedFactories();

                return empty($allowedFactories) ? [null] : $allowedFactories;
            }
        }

        return [];
    }

    /**
     * Determines possible classes for the new instance
     *
     * Modified from \PHPStan\Rules\Classes\InstantiationRule::getClassNames
     *
     * @param \PhpParser\Node\Expr\New_ $node $node
     *
     * @return array<int, array{string, bool}>
     *
     * @psalm-return  array<array{string, bool}>
     *
     * @license https://github.com/phpstan/phpstan-src/blob/1.11.x/LICENSE
     * @author  Ondřej Mirtes et al. https://github.com/phpstan/phpstan-src/blame/1.11.x/src/Rules/Classes/InstantiationRule.php
     * @author  ebln
     *
     * @see     \PHPStan\Rules\Classes\InstantiationRule::getClassNames
     */
    private function getClassNames(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope): array
    {
        if ($node->class instanceof \PhpParser\Node\Name) {
            return [[$scope->resolveName($node->class), \true]];
        }
        if ($node->class instanceof \PhpParser\Node\Stmt\Class_) {
            $classNames = $scope->getType($node)->getObjectClassNames();
            if ([] === $classNames) {
                throw new \PHPStan\ShouldNotHappenException();
            }
            // Report back extended class!
            if ($node->class->extends) {
                return [[$node->class->extends->toString(), \true]];
            }

            return array_map(
                static fn (string $className) => [$className, \false],
                $classNames,
            );
        }
        $type = $scope->getType($node->class);

        return \array_merge(
            \array_map(static function (\PHPStan\Type\Constant\ConstantStringType $type): array {
                return [$type->getValue(), \true];
            }, $type->getConstantStrings()),
            \array_map(static function (string $name): array {
                return [$name, \false];
            }, $type->getObjectClassNames())
        );
    }
}
