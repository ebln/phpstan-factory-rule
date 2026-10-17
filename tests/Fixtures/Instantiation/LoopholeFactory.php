<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

class LoopholeFactory
{
    // the union keeps both constants; PHPStan types the `new` as an error because one class is missing
    public function unknownClassInUnion(bool $toggle): object
    {
        $class = $toggle ? '\Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\LoopholeProduct' : 'Nope\Missing';

        return new $class();
    }

    // documented loophole: the constant is absorbed by the plain string, the type is `string`
    public function stringInUnion(bool $toggle, string $plain): object
    {
        $class = $toggle ? '\Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\LoopholeProduct' : $plain;

        return new $class();
    }
}
