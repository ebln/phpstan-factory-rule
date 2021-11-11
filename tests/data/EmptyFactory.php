<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\data;

use Test\Ebln\PHPStan\EnforceFactory\data\code\EmptyProduct;

class EmptyFactory
{
    public function class(): EmptyProduct
    {
        return new EmptyProduct();
    }
}
