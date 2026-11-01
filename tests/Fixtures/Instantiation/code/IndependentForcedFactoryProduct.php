<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code;

use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\IndependentFactory;

#[\Ebln\Attrib\ForceFactory(IndependentFactory::class, '\test\ebln\phpstan\enforcefactory\fixtures\instantiation\independentfactory')]
class IndependentForcedFactoryProduct
{
}
