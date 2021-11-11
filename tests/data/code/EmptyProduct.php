<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\data\code;

use Ebln\PHPStan\EnforceFactory\ForceFactoryInterface;

class EmptyProduct implements ForceFactoryInterface
{
    public static function getFactories(): array
    {
        return [];
    }
}
