<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataMixed\code;

use Ebln\Attrib\ForceFactory;
use Test\Ebln\PHPStan\EnforceFactory\dataMixed\AttributeFactory;

#[ForceFactory(AttributeFactory::class)]
class AttributeProduct
{
}
