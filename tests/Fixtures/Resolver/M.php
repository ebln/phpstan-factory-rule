<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Resolver;

use Ebln\Attrib\ForceFactory;

#[ForceFactory('\TEST\ebln\phpstan\enforcefactory\fixtures\resolver\real', 'Nope\Missing', M::VIA_CONST)]
class M
{
    public const VIA_CONST = Real::class;
}
