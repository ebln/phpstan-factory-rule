<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\EmptyProduct;

final class EmptyFactory
{
    public function class(): EmptyProduct
    {
        return new EmptyProduct();
    }

    public static function createSelf(): self
    {
        return new self();
    }

    public static function createStatic(): static
    {
        return new static();
    }
}
