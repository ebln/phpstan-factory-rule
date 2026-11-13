<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\ForceFactoryReflectionRule;
use Ebln\PHPStan\EnforceFactory\InstantiationChecker;
use PHPStan\Php\PhpVersion;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends RuleTestCase<ForceFactoryReflectionRule>
 */
#[CoversClass(ForceFactoryReflectionRule::class)]
#[CoversClass(InstantiationChecker::class)]
final class ForceFactoryReflectionLazyObjectsTest extends RuleTestCase
{
    private const NS = 'Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\\';

    private const PRODUCT = self::NS . 'code\ReflectedProduct';

    public function testLazyObjectMethodsAreReported(): void
    {
        self::assertSame(80400, self::getContainer()->getByType(PhpVersion::class)->getVersionId());

        $this->analyse([__DIR__ . '/Fixtures/Reflection/LazyReflection.php'], [
            self::outside('newLazyGhost', 13),
            self::outside('newLazyProxy', 18),
        ]);
    }

    public function testListedFactoryMayCreateLazyObjects(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Reflection/ReflectionFactory.php'], []);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../rules.neon', __DIR__ . '/Fixtures/Reflection/php84.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ForceFactoryReflectionRule::class);
    }

    /**
     * @return array{string, int, string}
     */
    private static function outside(string $method, int $line): array
    {
        $factory = self::NS . 'ReflectionFactory';

        return [
            sprintf('ReflectionClass::%s() creates %s outside its factories: %s must be instantiated by %s.', $method, self::PRODUCT, self::PRODUCT, $factory),
            $line,
            'Move this instantiation into ' . $factory . ', or add ' . self::NS . 'LazyReflection to the #[ForceFactory] attribute of ' . self::PRODUCT . '.',
        ];
    }
}
