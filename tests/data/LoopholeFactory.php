<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\data;

class LoopholeFactory
{
    public function variableUninferable(bool $toggle): void
    {
        if ($toggle) {
            $class = '\Test\Ebln\PHPStan\EnforceFactory\data\code\ForcedFactoryProduct';
        } else {
            $class = 'Hello world-' . random_int(10, 99);
        }

        $new = new $class();
    }
}
