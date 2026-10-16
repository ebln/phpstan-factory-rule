<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\AnonymousProduct;

class AnonymousInsideFactory
{
    public function viaAnonymousClass(): AnonymousProduct
    {
        $helper = new class() {
            public function build(): AnonymousProduct
            {
                return new AnonymousProduct();
            }
        };

        return $helper->build();
    }
}
