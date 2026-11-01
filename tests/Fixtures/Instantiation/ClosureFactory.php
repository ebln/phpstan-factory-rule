<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\ClosureProduct;

class ClosureFactory
{
    public function viaArrowFunction(): ClosureProduct
    {
        return (fn () => new ClosureProduct())();
    }

    public function viaClosure(): ClosureProduct
    {
        $create = function () {
            return new ClosureProduct();
        };

        return $create();
    }
}
