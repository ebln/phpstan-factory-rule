<?php

declare(strict_types=1);

namespace Ebln\Attrib;

/**
 * Marks a class as instantiable only by the listed factory classes.
 *
 * Enforced by the PHPStan rule in ebln/phpstan-factory-rule; the attribute itself
 * is never instantiated during analysis, so it carries no validation.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class ForceFactory
{
    /** @var list<class-string> */
    public array $factories;

    /** @param class-string ...$factories */
    public function __construct(string ...$factories)
    {
        $this->factories = array_values(array_unique($factories));
    }
}
