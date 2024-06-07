<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code;

use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\IndependentFactory;

#[\Ebln\Attrib\ForceFactory(IndependentFactory::class)]
#[\INVALID\NOT\FOUND\ATTRIBUTE(IndependentFactory::class)]
class IndependentForcedFactoryProduct
{
}
