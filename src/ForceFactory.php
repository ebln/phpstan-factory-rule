<?php

declare(strict_types=1);

namespace Ebln\PHPStan\EnforceFactory;

/**
 * Marks classes to be instanciated by certain factories
 *
 * If used together with ForceFactoryInterface
 *   the configured factories must be congruent!
 *   This is enforced for PHP 8 and later.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ForceFactory
{
    /** @var array<int, class-string> */
    private array $allowedFactories;

    /** @param class-string ...$factories */
    public function __construct(string ...$factories)
    {
        $allowedFactories = [];
        foreach ($factories as $factory) {
            $allowedFactories[$factory] = $factory;
        }

        $this->allowedFactories = array_values($allowedFactories);
    }

    /**
     * @return array<int, class-string>
     */
    public function getAllowedFactories(): array
    {
        return $this->allowedFactories;
    }
}
