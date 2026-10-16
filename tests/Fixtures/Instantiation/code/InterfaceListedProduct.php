<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code;

use Ebln\Attrib\ForceFactory;

#[ForceFactory(FactoryInterface::class)]
class InterfaceListedProduct
{
}
