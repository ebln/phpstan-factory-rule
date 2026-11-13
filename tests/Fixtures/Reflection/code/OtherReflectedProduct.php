<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\code;

use Ebln\Attrib\ForceFactory;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\ReflectionFactory;

#[ForceFactory(ReflectionFactory::class)]
class OtherReflectedProduct
{
}
