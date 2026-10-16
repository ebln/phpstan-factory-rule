<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\FactoryInterface;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\InterfaceListedProduct;

class InterfaceFactory implements FactoryInterface
{
    public function create(): InterfaceListedProduct
    {
        return new InterfaceListedProduct();
    }
}
