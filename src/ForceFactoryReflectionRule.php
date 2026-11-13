<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Type\TypeCombinator;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class ForceFactoryReflectionRule implements Rule
{
    /**
     * The methods of ReflectionClass that create an instance: lower-case name (PHP ignores the case
     * of a method name) to the canonical name that the message shows.
     */
    private const METHODS = [
        'newinstance'                   => 'newInstance',
        'newinstanceargs'               => 'newInstanceArgs',
        'newinstancewithoutconstructor' => 'newInstanceWithoutConstructor',
    ];

    public function __construct(private readonly InstantiationChecker $checker) {}

    public function getNodeType(): string
    {
        return Node\Expr\MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier) {
            return []; // a method name from an expression is a documented loophole
        }
        $method = self::METHODS[$node->name->toLowerString()] ?? null;
        if (null === $method) {
            return [];
        }

        $errors = [];
        foreach ($this->createdClasses($node, $scope) as $class) {
            $error = $this->checker->checkReflection($class, $scope, $method);
            if (null !== $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * The classes named by the template type T of the caller. The return type of the call is not used:
     * it is an error type when the caller is nullable. Plain ReflectionClass, ReflectionClass<object>
     * and callers of another type give no class reflection and are skipped.
     *
     * @return list<ClassReflection>
     */
    private function createdClasses(Node\Expr\MethodCall $node, Scope $scope): array
    {
        $caller  = TypeCombinator::removeNull($scope->getType($node->var));
        $classes = [];
        foreach ($caller->getTemplateType(\ReflectionClass::class, 'T')->getObjectClassReflections() as $reflection) {
            $classes[$reflection->getName()] = $reflection;
        }

        return array_values($classes);
    }
}
