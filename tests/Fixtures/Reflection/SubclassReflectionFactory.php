<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code\ReflectedProduct;

class SubclassReflectionFactory extends ReflectionFactory
{
    public function createInSubclass(): object
    {
        return (new \ReflectionClass(ReflectedProduct::class))->newInstance();
    }
}
