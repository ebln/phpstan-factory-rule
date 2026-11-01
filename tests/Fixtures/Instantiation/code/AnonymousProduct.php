<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code;

use Ebln\Attrib\ForceFactory;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\AnonymousInsideFactory;

#[ForceFactory(AnonymousInsideFactory::class)]
class AnonymousProduct
{
}
