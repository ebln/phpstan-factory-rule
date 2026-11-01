<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\ForcedFactoryProduct;

class UnknownClassFactory
{
    public function unknown(): object
    {
        return new \Nope\Missing();
    }

    public function rogue(): ForcedFactoryProduct
    {
        return new ForcedFactoryProduct();
    }
}
