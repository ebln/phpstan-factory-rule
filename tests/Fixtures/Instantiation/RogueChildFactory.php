<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\MarkedChildProduct;

class RogueChildFactory
{
    public function create(): MarkedChildProduct
    {
        return new MarkedChildProduct();
    }
}
