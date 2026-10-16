<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

use Ebln\Attrib\ForceFactory;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;

/**
 * Reads the #[ForceFactory] attribute through PHPStan's reflection, so the attribute
 * class is never instantiated and non-constant arguments cannot break the analysis.
 */
final class AllowedFactoriesResolver
{
    /** @var array<string, null|list<class-string>> */
    private array $allowedByClass = [];

    public function __construct(private readonly ReflectionProvider $reflectionProvider) {}

    /**
     * The factory names written on this class's own attribute, as written, deduplicated.
     *
     * @return null|list<string> null when the class carries no attribute; [] when it carries one
     *                           whose arguments PHPStan cannot resolve to constant strings
     */
    public function declaredFactories(ClassReflection $class): ?array
    {
        foreach ($class->getAttributes() as $attribute) {
            if (ForceFactory::class !== $attribute->getName()) {
                continue;
            }
            $factories = $attribute->getArgumentTypes()['factories'] ?? null;
            if (null === $factories) {
                return [];
            }
            $names = [];
            foreach ($factories->getConstantStrings() as $constantString) {
                $names[$constantString->getValue()] = $constantString->getValue();
            }

            return array_values($names);
        }

        return null;
    }

    /**
     * The canonical names of the existing factory classes from the nearest attribute in the
     * class's parent chain ("nearest attribute wins").
     *
     * @return null|list<class-string> null when no class in the chain carries the attribute
     */
    public function allowedFactories(ClassReflection $class): ?array
    {
        $key = $class->getName();
        if (array_key_exists($key, $this->allowedByClass)) {
            return $this->allowedByClass[$key];
        }

        $declared = null;
        for ($current = $class; null !== $current; $current = $current->getParentClass()) {
            $declared = $this->declaredFactories($current);
            if (null !== $declared) {
                break;
            }
        }

        return $this->allowedByClass[$key] = null === $declared ? null : $this->canonicalise($declared);
    }

    /**
     * @param list<string> $names
     *
     * @return list<class-string>
     */
    private function canonicalise(array $names): array
    {
        $canonical = [];
        foreach ($names as $name) {
            $name = ltrim($name, '\\');
            if ('' === $name || !$this->reflectionProvider->hasClass($name)) {
                continue; // the definition rule reports unknown factories
            }
            $className             = $this->reflectionProvider->getClass($name)->getName();
            $canonical[$className] = $className;
        }

        return array_values($canonical);
    }
}
