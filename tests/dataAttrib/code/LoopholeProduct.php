<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code;

use Ebln\PHPStan\EnforceFactory\ForceFactory;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\ForcedFactory;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\TraitFactory;

#[ForceFactory(ForcedFactory::class, TraitFactory::class)]
class LoopholeProduct
{
}
