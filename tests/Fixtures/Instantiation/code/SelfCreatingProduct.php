<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code;

use Ebln\Attrib\ForceFactory;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\ForcedFactory;

#[ForceFactory(ForcedFactory::class)]
class SelfCreatingProduct
{
    public static function viaSelf(): self
    {
        return new self();
    }

    public static function viaStatic(): static
    {
        return new static();
    }
}
