<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\ForcedFactoryProduct;

class SubclassOfFactory extends ForcedFactory
{
    public function createInSubclass(): ForcedFactoryProduct
    {
        return new ForcedFactoryProduct();
    }
}
