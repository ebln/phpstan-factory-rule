<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

abstract class StaticConstructorBase
{
    public static function create(): static
    {
        return new static();
    }
}
