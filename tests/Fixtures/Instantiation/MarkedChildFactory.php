<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\MarkedChildProduct;

// a subclass of a factory of ForcedFactoryProduct, so MarkedChildProduct narrows its parent's factories
class MarkedChildFactory extends ForcedFactory
{
    public function create(): MarkedChildProduct
    {
        return new MarkedChildProduct();
    }
}
