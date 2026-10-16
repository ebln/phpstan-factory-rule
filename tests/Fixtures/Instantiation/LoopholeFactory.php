<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

class LoopholeFactory
{
    public function variableUninferable(bool $toggle): object
    {
        if ($toggle) {
            $class = '\Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code\LoopholeProduct';
        } else {
            $class = 'Hello world-' . random_int(10, 99);
        }

        return new $class();
    }
}
