<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib;

use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\IndependentForcedFactoryProduct;

class IndependentFactory
{
    public function independentClass(): IndependentForcedFactoryProduct
    {
        return new IndependentForcedFactoryProduct();
    }
}
