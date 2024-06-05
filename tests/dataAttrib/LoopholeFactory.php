<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib;

class LoopholeFactory
{
    public function variableUninferable(bool $toggle): void
    {
        if ($toggle) {
            $class = '\Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\LoopholeProduct';
        } else {
            $class = 'Hello world-' . random_int(10, 99);
        }

        $new = new $class();
    }
}
