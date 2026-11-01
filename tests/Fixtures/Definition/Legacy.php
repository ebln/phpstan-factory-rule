<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Definition;

use Ebln\PHPStan\EnforceFactory\ForceFactoryInterface;

class Legacy implements ForceFactoryInterface
{
    public static function getFactories(): array
    {
        return [RealFactory::class];
    }
}
