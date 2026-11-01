<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code;

use Ebln\Attrib\ForceFactory;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\MarkedChildFactory;

#[ForceFactory(MarkedChildFactory::class)]
class MarkedChildProduct extends ForcedFactoryProduct
{
}
