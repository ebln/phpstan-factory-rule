<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Decides whether the code in a scope may create an instance of a class marked with #[ForceFactory].
 * All rules that report instantiations share this check, so they allow and forbid the same places.
 */
final class InstantiationChecker
{
    public function __construct(private readonly AllowedFactoriesResolver $resolver) {}

    public function checkNew(ClassReflection $product, Scope $scope): ?IdentifierRuleError
    {
        return $this->check($product, $scope, 'ebln.forceFactory.outsideFactory', static fn (string $name, string $factories): string => sprintf('%s must be instantiated by %s.', $name, $factories));
    }

    /** @param string $method the canonical name of the ReflectionClass method that creates the instance */
    public function checkReflection(ClassReflection $product, Scope $scope, string $method): ?IdentifierRuleError
    {
        return $this->check(
            $product,
            $scope,
            'ebln.forceFactory.reflectionOutsideFactory',
            static fn (string $name, string $factories): string => sprintf(
                'ReflectionClass::%s() creates %s outside its factories: %s must be instantiated by %s.',
                $method,
                $name,
                $name,
                $factories,
            ),
        );
    }

    /** @param callable(string, string): string $message builds the outside-factory message from the product name and the factory list */
    private function check(ClassReflection $product, Scope $scope, string $identifier, callable $message): ?IdentifierRuleError
    {
        $allowedFactories = $this->resolver->allowedFactories($product);
        if (null === $allowedFactories) {
            return null; // no attribute in the chain
        }
        if ([] === $allowedFactories) {
            return $this->noFactories($product); // fail closed: a list that resolves to nothing allows nobody
        }

        $currentClass = $scope->getClassReflection();
        if (null !== $currentClass && in_array($currentClass->getName(), $allowedFactories, true)) {
            return null; // happy case: created within an allowed factory class
        }

        return RuleErrorBuilder::message($message($product->getName(), implode(' or ', $allowedFactories)))
            ->identifier($identifier)
            ->tip($this->tip($product, $allowedFactories, $currentClass))
            ->build()
        ;
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
}
