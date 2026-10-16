<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
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
                continue; // newly instantiated class carries no ForceFactory attribute
            }

            $className = $class->getName();
            if ([] === $allowedFactories) {
                $errors[] = RuleErrorBuilder::message($className . ' has no factories defined!')
                    ->identifier('ebln.forceFactory.bogusDefinition')
                    ->build()
                ;

                continue; // bogus configuration
            }

            $currentClass = $scope->getClassReflection()?->getName();
            if (null !== $currentClass && in_array($currentClass, $allowedFactories, true)) {
                continue; // happy case: created within an allowed factory class
            }

            $errors[] = RuleErrorBuilder::message(
                $className . ' must be instantiated by ' . implode(' or ', $allowedFactories) . '!'
            )->identifier('ebln.forceFactory.outOfFactoryInstanciation')
                ->tip('Only use ' . implode(' or ', $allowedFactories) . ' to create an instance of ' . $className . '!')
                ->build()
            ;
        }

        return $errors;
    }

    /**
     * Every class the expression may instantiate, as PHPStan infers it: a named class,
     * the parent of an anonymous class, or the object/class-string type of a variable.
     * Unknown classes are not part of the inferred type and so are silently skipped.
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

        return array_values($classes);
    }
}
