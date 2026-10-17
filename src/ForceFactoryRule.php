<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node\Expr\New_>
 */
class ForceFactoryRule implements Rule
{
    public function __construct(private readonly AllowedFactoriesResolver $resolver) {}

    public function getNodeType(): string
    {
        return Node\Expr\New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($this->instantiatedClasses($node, $scope) as $class) {
            $allowedFactories = $this->resolver->allowedFactories($class);
            if (null === $allowedFactories) {
                continue; // no attribute in the chain
            }
            if ([] === $allowedFactories) {
                $errors[] = $this->noFactories($class); // fail closed: a list that resolves to nothing allows nobody

                continue;
            }

            $currentClass = $scope->getClassReflection();
            if (null !== $currentClass && in_array($currentClass->getName(), $allowedFactories, true)) {
                continue; // happy case: created within an allowed factory class
            }

            $errors[] = RuleErrorBuilder::message(
                sprintf('%s must be instantiated by %s.', $class->getName(), implode(' or ', $allowedFactories))
            )
                ->identifier('ebln.forceFactory.outsideFactory')
                ->tip($this->tip($class, $allowedFactories, $currentClass))
                ->build()
            ;
        }

        return $errors;
    }

    private function noFactories(ClassReflection $product): IdentifierRuleError
    {
        $holder = $this->resolver->attributeHolder($product) ?? $product;

        return RuleErrorBuilder::message(sprintf(
            '%s cannot be instantiated: #[ForceFactory] on %s names no factory PHPStan can resolve.',
            $product->getName(),
            $holder->getName(),
        ))
            ->identifier('ebln.forceFactory.noFactories')
            ->tip(sprintf('Fix the attribute on %s: list at least one existing class name constant.', $holder->getName()))
            ->build()
        ;
    }

    /** @param list<class-string> $allowedFactories */
    private function tip(ClassReflection $product, array $allowedFactories, ?ClassReflection $currentClass): string
    {
        $tip = 'Move this instantiation into ' . implode(' or ', $allowedFactories);
        if (null === $currentClass || $currentClass->isAnonymous()) {
            return $tip . '.'; // nothing nameable to add to the attribute
        }

        // name the class that carries the attribute: an attribute added to a subclass would replace the inherited list
        $holder    = $this->resolver->attributeHolder($product) ?? $product;
        $ancestor  = $this->resolver->markedAncestor($holder);
        $inherited = null === $ancestor ? [] : ($this->resolver->allowedFactories($ancestor) ?? []);
        if ([] !== $inherited && !$this->resolver->isFactoryOrSubclass($currentClass, $inherited)) {
            return $tip . '.'; // the definition rule would reject this class: it does not narrow the ancestor's factories
        }

        return sprintf('%s, or add %s to the #[ForceFactory] attribute of %s.', $tip, $currentClass->getName(), $holder->getName());
    }

    /**
     * Every class the expression may instantiate, as PHPStan infers it: a named class,
     * the parent of an anonymous class, or the object/class-string type of a variable.
     * Unknown classes are not part of the inferred type and so are silently skipped.
     *
     * The constant strings of a class expression are read as well: one unknown name in a
     * union turns the type of the whole `new` into an error type that would hide the product.
     *
     * @return list<ClassReflection>
     */
    private function instantiatedClasses(Node\Expr\New_ $node, Scope $scope): array
    {
        $classes = [];
        foreach ($scope->getType($node)->getObjectClassReflections() as $reflection) {
            if ($reflection->isAnonymous()) {
                // an anonymous class is checked as its parent: the generated name tells the reader nothing
                $reflection = $reflection->getParentClass();
                if (null === $reflection) {
                    continue;
                }
            }
            $classes[$reflection->getName()] = $reflection;
        }
        if ($node->class instanceof Node\Expr) {
            foreach ($scope->getType($node->class)->getConstantStrings() as $constantString) {
                $reflection = $this->resolver->classNamed($constantString->getValue());
                if (null !== $reflection) {
                    $classes[$reflection->getName()] = $reflection;
                }
            }
        }

        return array_values($classes);
    }
}
