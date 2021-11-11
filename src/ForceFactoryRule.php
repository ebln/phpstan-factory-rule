<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements \PHPStan\Rules\Rule<Node\Expr\New_>
 */
class ForceFactoryRule implements Rule
{
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
            $allowedFactories = $this->getAllowedFactories($class);
            if (null === $allowedFactories) {
                continue; // newly instantiated class dowsn't implement ForceFactory interface
            }

            if (empty($allowedFactories)) {
                $errors[] = RuleErrorBuilder::message(
                    ltrim($class, '\\') . ' cannot be instantiated by other classes; see ' . ForceFactoryInterface::class
                )->build();

                continue;
            }
            /** @psalm-suppress PossiblyNullReference | sad that even phpstan cannot infer that from isInClass */
            if (
                $scope->isInClass()
                && null !== $scope->getClassReflection()
                && in_array($scope->getClassReflection()->getName(), $allowedFactories, true)
            ) {
                continue; // happy case: ForceFactoryInterface got created within an allowed class
            }

            $errors[] = RuleErrorBuilder::message(
                ltrim($class, '\\') . ' must be instantiated by ' . implode(' or ', $allowedFactories) . '!'
            )->build();
        }

        return $errors;
    }

    /**
     * @return null|string[] List of FQCNs
     * @phpstan-return null|class-string[]
     */
    private function getAllowedFactories(string $className): ?array
    {
        if (!is_a($className, ForceFactoryInterface::class, true)) {
            return null;
        }

        return $className::getFactories();
    }

    /**
     * Determines possible classes for the new instance
     *
     * Modified from \PHPStan\Rules\Classes\InstantiationRule::getClassNames
     *
     * @param \PhpParser\Node\Expr\New_ $node $node
     *
     * @return array<int, array{string, bool}>
     * @psalm-return  array<array{string, bool}>
     *
     * @license https://github.com/phpstan/phpstan/blob/1.1.2/LICENSE
     * @author  Ondřej Mirtes et al. https://github.com/phpstan/phpstan-src/blob/0.12.x/src/Rules/Classes/InstantiationRule.php#blob_contributors_box
     * @author  ebln
     *
     * @see     \PHPStan\Rules\Classes\InstantiationRule::getClassNames
     */
    private function getClassNames(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope): array
    {
        if ($node->class instanceof \PhpParser\Node\Name) {
            return [[(string)$node->class, \true]];
        }
        if ($node->class instanceof \PhpParser\Node\Stmt\Class_) {
            $anonymousClassType = $scope->getType($node);
            if (!$anonymousClassType instanceof \PHPStan\Type\TypeWithClassName) {
                throw new \PHPStan\ShouldNotHappenException();
            }
            // Report back extended class!
            if ($node->class->extends) {
                return [[$node->class->extends->toString(), \true]];
            }

            // we don't care about the anonymous class' name and abort processing early
            return [[$anonymousClassType->getClassName(), \false]];
        }
        $type = $scope->getType($node->class);

        return \array_merge(
            \array_map(static function (\PHPStan\Type\Constant\ConstantStringType $type): array {
                return [$type->getValue(), \true];
            }, \PHPStan\Type\TypeUtils::getConstantStrings($type)),
            \array_map(static function (string $name): array {
                return [$name, \false];
            }, \PHPStan\Type\TypeUtils::getDirectClassNames($type))
        );
    }
}
