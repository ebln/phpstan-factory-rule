<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\data;

use Test\Ebln\PHPStan\EnforceFactory\data\code\ExtendedProduct;
use Test\Ebln\PHPStan\EnforceFactory\data\code\ForcedFactoryProduct;
use Test\Ebln\PHPStan\EnforceFactory\data\code\FreeProduct;

class RogueFactory
{
    public function class(): ForcedFactoryProduct
    {
        return new ForcedFactoryProduct();
    }

    public function classVariable(): void
    {
        $class = ForcedFactoryProduct::class;

        $new = new $class();
    }

    public function stringVariable(): void
    {
        $class = '\Test\Ebln\PHPStan\EnforceFactory\data\code\ForcedFactoryProduct';

        $new = new $class();
    }

    public function variableMixed(bool $toggle): void
    {
        if ($toggle) {
            $class = '\Test\Ebln\PHPStan\EnforceFactory\data\code\ForcedFactoryProduct';
        } else {
            $class = ForcedFactoryProduct::class;
        }

        $new = new $class();
    }

    public function variableMixedProducts(bool $toggle): void
    {
        if ($toggle) {
            $class = '\Test\Ebln\PHPStan\EnforceFactory\data\code\ForcedFactoryProduct';
        } else {
            $class = FreeProduct::class;
        }

        $new = new $class();
    }

    public function anonymousExtending(): void
    {
        $x = new class() extends ForcedFactoryProduct
        {
            public function foo(): string
            {
                return 'bar';
            }
        };

        $bar = $x->foo();
    }

    public function anonymousExtendingSquare(): void
    {
        $x = new class() extends ExtendedProduct
        {
            public function foo(): string
            {
                return 'bar';
            }
        };

        $bar = $x->foo();
    }

    public function anonymousPassing(): void
    {
        $x = new class()
        {
            public function foo(): string
            {
                return 'bar';
            }
        };

        $bar = $x->foo();
    }

    public static function staticClass(): ForcedFactoryProduct
    {
        return new ForcedFactoryProduct();
    }
}
