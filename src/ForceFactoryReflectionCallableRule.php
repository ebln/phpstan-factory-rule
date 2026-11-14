<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\MethodCallableNode;
use PHPStan\Rules\Rule;

/**
 * PHPStan passes a first-class callable such as `$reflection->newInstance(...)` to rules as a MethodCallableNode, not as a MethodCall.
 * The error is reported where the Closure is created: nothing checks the later call of the Closure.
 *
 * @implements Rule<MethodCallableNode>
 */
final class ForceFactoryReflectionCallableRule implements Rule
{
    public function __construct(private readonly ForceFactoryReflectionRule $rule) {}

    public function getNodeType(): string
    {
        return MethodCallableNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return $this->rule->processNode($node->getOriginalNode(), $scope);
    }
}
