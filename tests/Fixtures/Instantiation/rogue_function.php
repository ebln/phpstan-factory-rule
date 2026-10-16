<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\ForcedFactoryProduct;

function rogue_function(): ForcedFactoryProduct
{
    return new ForcedFactoryProduct();
}
