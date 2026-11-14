<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code\ReflectedProduct;

class ReflectionFactory
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

    public function viaClosure(): \Closure
    {
        return static fn (): object => (new \ReflectionClass(ReflectedProduct::class))->newInstance();
    }

    public function viaArrowFunction(): \Closure
    {
        return fn (): object => (new \ReflectionClass(ReflectedProduct::class))->newInstanceArgs([]);
    }

    public function viaCallable(): \Closure
    {
        $reflection = new \ReflectionClass(ReflectedProduct::class);

        return $reflection->newInstance(...);
    }

    public function viaNewLazyGhost(): object
    {
        return (new \ReflectionClass(ReflectedProduct::class))->newLazyGhost(static function (object $ghost): void {});
    }

    public function viaNewLazyProxy(): object
    {
        return (new \ReflectionClass(ReflectedProduct::class))->newLazyProxy(static fn (): object => new \stdClass());
    }
}
