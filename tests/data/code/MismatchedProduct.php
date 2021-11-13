<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\data\code;

use Ebln\PHPStan\EnforceFactory\ForceFactory;
use Ebln\PHPStan\EnforceFactory\ForceFactoryInterface;
use Test\Ebln\PHPStan\EnforceFactory\data\AttributeFactory;
use Test\Ebln\PHPStan\EnforceFactory\data\ForcedFactory;

#[ForceFactory(AttributeFactory::class)]
class MismatchedProduct implements ForceFactoryInterface
{
    public static function getFactories(): array
    {
        return [ForcedFactory::class, AttributeFactory::class];
    }
}
