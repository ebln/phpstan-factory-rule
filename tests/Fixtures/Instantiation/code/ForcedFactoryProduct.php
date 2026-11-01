<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code;

use Ebln\Attrib\ForceFactory;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\ForcedFactory;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\TraitFactory;

#[ForceFactory(ForcedFactory::class, TraitFactory::class)]
class ForcedFactoryProduct
{
}
