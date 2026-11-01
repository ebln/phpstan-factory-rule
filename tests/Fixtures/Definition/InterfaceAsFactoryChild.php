<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Definition;

use Ebln\Attrib\ForceFactory;

#[ForceFactory(FactoryContract::class)]
class InterfaceAsFactoryChild extends MarkedParent
{
}
