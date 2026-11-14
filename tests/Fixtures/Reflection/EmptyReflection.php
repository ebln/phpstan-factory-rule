<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code\EmptyReflectedProduct;

class EmptyReflection
{
    public function viaNewInstance(): object
    {
        return (new \ReflectionClass(EmptyReflectedProduct::class))->newInstance();
    }
}
