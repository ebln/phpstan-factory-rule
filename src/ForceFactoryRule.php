<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<Node\Expr\New_>
 */
class ForceFactoryRule implements Rule
{
    public function __construct(private readonly AllowedFactoriesResolver $resolver, private readonly InstantiationChecker $checker) {}

    public function getNodeType(): string
    {
        return Node\Expr\New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($this->instantiatedClasses($node, $scope) as $class) {
            $error = $this->checker->checkNew($class, $scope);
            if (null !== $error) {
                $errors[] = $error;
            }
        }

        return $errors;
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
