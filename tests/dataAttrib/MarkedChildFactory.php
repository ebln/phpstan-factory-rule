<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib;

use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\MarkedChildProduct;

class MarkedChildFactory
{
    public function create(): MarkedChildProduct
    {
        return new MarkedChildProduct();
    }
}
