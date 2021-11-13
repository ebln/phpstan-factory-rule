<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\data;

use Test\Ebln\PHPStan\EnforceFactory\data\code\AttributeProduct;
use Test\Ebln\PHPStan\EnforceFactory\data\code\MismatchedProduct;
use Test\Ebln\PHPStan\EnforceFactory\data\code\MixedProduct;

class AttributeFactory
{
    public function allowedAttribute(): void
    {
        $test = new AttributeProduct();
    }

    public function allowedMix(): void
    {
        $test = new MixedProduct();
    }

    public function failingMismatch(): void
    {
        $test = new MismatchedProduct();
    }
}
