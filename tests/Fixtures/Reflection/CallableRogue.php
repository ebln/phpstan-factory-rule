<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code\ReflectedProduct;

class CallableRogue
{
    public function viaCallable(): \Closure
    {
        $reflection = new \ReflectionClass(ReflectedProduct::class);

        return $reflection->newInstance(...);
    }
}
