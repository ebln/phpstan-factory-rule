<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Definition;

use Ebln\Attrib\ForceFactory;

#[ForceFactory(NonConstant::ONE)]
class NonConstant
{
    public const ONE = 1;
}
