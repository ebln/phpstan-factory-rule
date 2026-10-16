<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib;

use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\ForcedFactoryProduct;

class TypedVariableFactory
{
    public function fromObject(ForcedFactoryProduct $product): ForcedFactoryProduct
    {
        return new $product();
    }

    /** @param class-string<ForcedFactoryProduct> $class */
    public function fromClassString(string $class): ForcedFactoryProduct
    {
        return new $class();
    }

    public function fromString(string $class): object
    {
        return new $class();
    }
}
