<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code\OtherReflectedProduct;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code\ReflectedProduct;

class RogueReflection
{
    public function viaNewInstance(): object
    {
        return (new \ReflectionClass(ReflectedProduct::class))->newInstance();
    }

    public function viaNewInstanceArgs(): object
    {
        return (new \ReflectionClass(ReflectedProduct::class))->newInstanceArgs([]);
    }

    public function viaNewInstanceWithoutConstructor(): object
    {
        return (new \ReflectionClass(ReflectedProduct::class))->newInstanceWithoutConstructor();
    }

    public function viaMiscasedName(): object
    {
        return (new \ReflectionClass(ReflectedProduct::class))->NewInstance();
    }

    /** @param null|\ReflectionClass<ReflectedProduct> $reflection */
    public function viaNullsafeCall(?\ReflectionClass $reflection): ?object
    {
        return $reflection?->newInstance();
    }

    public function viaReflectionObject(ReflectedProduct $product): object
    {
        return (new \ReflectionObject($product))->newInstance();
    }

    /** @param \ReflectionClass<ReflectedProduct>|\ReflectionClass<OtherReflectedProduct> $reflection */
    public function viaUnion(\ReflectionClass $reflection): object
    {
        return $reflection->newInstance();
    }

    /** @param null|\ReflectionClass<ReflectedProduct> $reflection */
    public function viaNullableCaller(?\ReflectionClass $reflection): object
    {
        return $reflection->newInstance();
    }
}
