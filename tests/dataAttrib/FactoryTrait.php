<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib;

use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\ForcedFactoryProduct;

trait FactoryTrait
{
    public function traitedClass(): ForcedFactoryProduct
    {
        return new ForcedFactoryProduct();
    }

    public function traitedClassVariable(): void
    {
        $class = ForcedFactoryProduct::class;

        $new = new $class();
    }

    public function traitedStringVariable(): void
    {
        $class = '\Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\ForcedFactoryProduct';

        $new = new $class();
    }
}
