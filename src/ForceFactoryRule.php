<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use Ebln\Attrib\ForceFactory;
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
    public function getNodeType(): string
    {
        return Node\Expr\New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($this->instantiatedClasses($node, $scope) as $class) {
            $allowedFactories = $this->getFactoriesFromAttribute($class);
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

            if (
                $scope->isInClass()
                && null !== $scope->getClassReflection() /** @phpstan-ignore notIdentical.alwaysTrue */
                && in_array($scope->getClassReflection()->getName(), $allowedFactories, true)
            ) {
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

    /** @return null|list<class-string> */
    private function getFactoriesFromAttribute(ClassReflection $class): ?array
    {
        $allowedFactories = [];
        $reflection       = $class;
        do {
            $allowedFactories = [...$allowedFactories, ...$this->getFactoriesFromAttributeByClass($reflection->getNativeReflection())];
        } while ($reflection = $reflection->getParentClass());

        if ([] === $allowedFactories) {
            return null;
        }
        $allowedFactories = array_values(array_filter($allowedFactories));
        sort($allowedFactories);

        return $allowedFactories;
    }

    /**
     * @param \ReflectionClass<object> $reflection
     *
     * @return array<int, null|class-string>
     */
    private function getFactoriesFromAttributeByClass(\ReflectionClass $reflection): array
    {
        foreach ($reflection->getAttributes() as $attribute) {
            if (ForceFactory::class === $attribute->getName()) {
                /** @var ForceFactory $forceFactory */
                $forceFactory     = $attribute->newInstance();
                $allowedFactories = $forceFactory->factories;

                return [] === $allowedFactories ? [null] : $allowedFactories;
            }
        }

        return [];
    }
}
