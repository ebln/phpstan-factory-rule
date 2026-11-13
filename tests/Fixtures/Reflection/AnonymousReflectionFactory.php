<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code\AnonymousReflectedProduct;

class AnonymousReflectionFactory
{
    public function viaAnonymousClass(): object
    {
        $helper = new class() {
            public function build(): object
            {
                return (new \ReflectionClass(AnonymousReflectedProduct::class))->newInstance();
            }
        };

        return $helper->build();
    }
}
