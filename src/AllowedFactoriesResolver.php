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
            if (0 !== strcasecmp(ForceFactory::class, $attribute->getName())) {
                continue; // PHP resolves class names case-insensitively; without arguments PHPStan keeps the written case
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

        $holder   = $this->attributeHolder($class);
        $declared = null === $holder ? null : $this->declaredFactories($holder);

        return $this->allowedByClass[$key] = null === $declared ? null : $this->canonicalise($declared);
    }

    /** The closest marked class strictly above the given class, or null when no ancestor carries the attribute. */
    public function markedAncestor(ClassReflection $class): ?ClassReflection
    {
        $parent = $class->getParentClass();

        return null === $parent ? null : $this->attributeHolder($parent);
    }

    /**
     * Whether the class is one of the given factories or a subclass of one: the factories a
     * marked subclass may list, given the factories of its marked ancestor.
     *
     * @param list<class-string> $factories
     */
    public function isFactoryOrSubclass(ClassReflection $class, array $factories): bool
    {
        return [] !== array_intersect([$class->getName(), ...$class->getParentClassesNames()], $factories);
    }

    /**
     * The class whose attribute governs the given class: itself, or its closest marked
     * ancestor. Null when no class in the chain carries the attribute.
     */
    public function attributeHolder(ClassReflection $class): ?ClassReflection
    {
        for ($current = $class; null !== $current; $current = $current->getParentClass()) {
            if (null !== $this->declaredFactories($current)) {
                return $current;
            }
        }

        return null;
    }

    /** The reflection of a class named by a constant string, or null when PHPStan does not know it. */
    public function classNamed(string $name): ?ClassReflection
    {
        $name = ltrim($name, '\\');

        return '' !== $name && $this->reflectionProvider->hasClass($name) ? $this->reflectionProvider->getClass($name) : null;
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
            $class = $this->classNamed($name);
            if (null === $class) {
                continue; // the definition rule reports unknown factories
            }
            $canonical[$class->getName()] = $class->getName();
        }

        return array_values($canonical);
    }
}
