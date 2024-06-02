<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib;

use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\EmptyProduct;

class EmptyFactory
{
    public function class(): EmptyProduct
    {
        return new EmptyProduct();
    }
}
