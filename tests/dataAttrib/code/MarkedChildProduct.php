<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code;

use Ebln\Attrib\ForceFactory;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\MarkedChildFactory;

#[ForceFactory(MarkedChildFactory::class)]
class MarkedChildProduct extends ForcedFactoryProduct
{
}
