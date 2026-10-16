<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\IndependentForcedFactoryProduct;

class IndependentFactory
{
    public function independentClass(): IndependentForcedFactoryProduct
    {
        return new IndependentForcedFactoryProduct();
    }
}
