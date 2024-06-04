<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataMixed;

use Test\Ebln\PHPStan\EnforceFactory\dataMixed\code\AttributeProduct;
use Test\Ebln\PHPStan\EnforceFactory\dataMixed\code\MixedProduct;

class RogueAttributeFactory
{
    public function forbiddenAttribute(): void
    {
        $test = new AttributeProduct();
    }

    public function forbiddenMix(): void
    {
        $test = new MixedProduct();
    }
}
