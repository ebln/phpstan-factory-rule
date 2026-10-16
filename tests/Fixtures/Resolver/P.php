<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Resolver;

use Ebln\Attrib\ForceFactory;

#[ForceFactory(A::class)]
class P
{
}
