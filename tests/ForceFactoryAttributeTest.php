<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\Attrib\ForceFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ForceFactory::class)]
final class ForceFactoryAttributeTest extends TestCase
{
    public function testDeduplicatesAndKeepsOrder(): void
    {
        self::assertSame(
            [self::class, ForceFactory::class],
            (new ForceFactory(self::class, ForceFactory::class, self::class))->factories,
        );
    }

    public function testEmptyListStaysEmpty(): void
    {
        self::assertSame([], (new ForceFactory())->factories);
    }
}
