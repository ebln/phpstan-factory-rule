<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code\ReflectedProduct;

class LazyReflection
{
    public function viaNewLazyGhost(): object
    {
        return (new \ReflectionClass(ReflectedProduct::class))->newLazyGhost(static function (object $ghost): void {});
    }

    public function viaNewLazyProxy(): object
    {
        return (new \ReflectionClass(ReflectedProduct::class))->newLazyProxy(static fn (): object => new \stdClass());
    }

    // these two change an object that exists already: the place that created it is checked
    public function viaResetAsLazyGhost(ReflectedProduct $product): void
    {
        (new \ReflectionClass(ReflectedProduct::class))->resetAsLazyGhost($product, static function (object $ghost): void {});
    }

    public function viaResetAsLazyProxy(ReflectedProduct $product): void
    {
        (new \ReflectionClass(ReflectedProduct::class))->resetAsLazyProxy($product, static fn (): object => new \stdClass());
    }
}
