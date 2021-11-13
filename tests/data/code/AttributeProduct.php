<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\data\code;

use Ebln\PHPStan\EnforceFactory\ForceFactory;
use Test\Ebln\PHPStan\EnforceFactory\data\AttributeFactory;

#[ForceFactory(AttributeFactory::class)]
class AttributeProduct
{
}
