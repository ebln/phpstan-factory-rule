<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Definition;

use Ebln\PHPStan\EnforceFactory\ForceFactoryInterface;

final class LegacyAnonymous
{
    public function make(): object
    {
        return new class() implements ForceFactoryInterface {
            public static function getFactories(): array
            {
                return [];
            }
        };
    }
}
