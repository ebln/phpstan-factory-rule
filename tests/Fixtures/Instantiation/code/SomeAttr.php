<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\code;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class SomeAttr
{
    public function __construct(public object $value)
    {
    }
}
