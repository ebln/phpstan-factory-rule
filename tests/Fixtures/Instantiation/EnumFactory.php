<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\EnumProduct;

enum EnumFactory
{
    case One;

    public function create(): EnumProduct
    {
        return new EnumProduct();
    }
}
