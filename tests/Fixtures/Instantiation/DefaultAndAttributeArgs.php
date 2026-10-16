<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\ArgsProduct;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\SomeAttr;

class DefaultAndAttributeArgs
{
    #[SomeAttr(new ArgsProduct())]
    public function withDefault(ArgsProduct $product = new ArgsProduct()): ArgsProduct
    {
        return $product;
    }
}
