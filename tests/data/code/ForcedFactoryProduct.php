<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\data\code;

use Ebln\PHPStan\EnforceFactory\ForceFactoryInterface;
use Test\Ebln\PHPStan\EnforceFactory\data\ForcedFactory;
use Test\Ebln\PHPStan\EnforceFactory\data\TraitFactory;

class ForcedFactoryProduct implements ForceFactoryInterface
{

    public static function getFactories(): array
    {
        return [ForcedFactory::class, TraitFactory::class];
    }
}
