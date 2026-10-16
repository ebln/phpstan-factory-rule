<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib;

use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\FactoryInterface;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\InterfaceListedProduct;

class InterfaceFactory implements FactoryInterface
{
    public function create(): InterfaceListedProduct
    {
        return new InterfaceListedProduct();
    }
}
