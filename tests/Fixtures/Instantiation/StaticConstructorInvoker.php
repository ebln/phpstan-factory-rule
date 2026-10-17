<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\StaticChildProduct;

class StaticConstructorInvoker
{
    public function create(): StaticChildProduct
    {
        return StaticChildProduct::create();
    }
}
