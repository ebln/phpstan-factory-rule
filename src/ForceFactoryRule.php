<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

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
                continue; // newly instantiated class dowsn't implement ForceFactory interface
            }

            if ([] === $allowedFactories) {
                $errors[] = RuleErrorBuilder::message(
                    ltrim($class, '\\') . ' has either no factories defined or a conflict between interface and attribute!'
                )->build();

                continue; // bogus configuration
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
     * @phpstan-param class-string $className
     *
     * @return null|string[] List of FQCNs
     * @phpstan-return null|class-string[]
     */
    private function getAllowedFactories(string $className): ?array
    {
        $allowedFactories = $this->getFactoriesFromAttribute($className);

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
     * @return array<class-string>
     */
    private function getFactoriesFromAttribute(string $className): ?array
    {
        if (\PHP_VERSION_ID < 80000 || !$this->reflectionProvider->hasClass($className)) {
            return null;
        }

        /**
         * FIXME!
         * https://github.com/phpstan/phpstan/discussions/5863
         * https://github.com/phpstan/phpstan/issues/5954
         */

        /** @var \ReflectionAttribute $attribute */
        foreach ($this->reflectionProvider->getClass($className)->getNativeReflection()->getAttributes() as $attribute) {
            if (ForceFactory::class === $attribute->getName()) {
                /** @var ForceFactory $forceFactory */
                $forceFactory     = $attribute->newInstance();
                $allowedFactories = $forceFactory->getAllowedFactories();
                sort($allowedFactories);

                return $allowedFactories;
            }
        }

        return null;
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
