<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code\ReflectedProduct;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code\UnmarkedReflectedProduct;

class NotReflecting
{
    public function plainReflectionClass(\ReflectionClass $reflection): object
    {
        return $reflection->newInstance();
    }

    public function classNameFromString(string $name): object
    {
        return (new \ReflectionClass($name))->newInstance();
    }

    public function unmarkedProduct(): object
    {
        return (new \ReflectionClass(UnmarkedReflectedProduct::class))->newInstance();
    }

    public function methodNameFromVariable(string $method): mixed
    {
        return (new \ReflectionClass(ReflectedProduct::class))->$method();
    }

    public function otherObjectWithNewInstance(): object
    {
        return (new class() {
            public function newInstance(): object
            {
                return new \stdClass();
            }
        })->newInstance();
    }

    public function otherReflectionMethods(): string
    {
        return (new \ReflectionClass(ReflectedProduct::class))->getName();
    }
}
