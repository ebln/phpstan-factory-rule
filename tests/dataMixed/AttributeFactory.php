<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataMixed;

use Test\Ebln\PHPStan\EnforceFactory\dataMixed\code\AttributeProduct;
use Test\Ebln\PHPStan\EnforceFactory\dataMixed\code\MismatchedProduct;
use Test\Ebln\PHPStan\EnforceFactory\dataMixed\code\MixedProduct;

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
