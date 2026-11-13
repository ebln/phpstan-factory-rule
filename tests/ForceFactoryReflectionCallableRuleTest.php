<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\ForceFactoryReflectionCallableRule;
use Ebln\PHPStan\EnforceFactory\ForceFactoryReflectionRule;
use Ebln\PHPStan\EnforceFactory\InstantiationChecker;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends RuleTestCase<ForceFactoryReflectionCallableRule>
 */
#[CoversClass(ForceFactoryReflectionCallableRule::class)]
#[CoversClass(ForceFactoryReflectionRule::class)]
#[CoversClass(InstantiationChecker::class)]
final class ForceFactoryReflectionCallableRuleTest extends RuleTestCase
{
    private const NS = 'Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\\';

    private const PRODUCT = self::NS . 'code\ReflectedProduct';

    public function testCallableOutsideFactoryIsReported(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Reflection/CallableRogue.php'], [
            [
                'ReflectionClass::newInstance() creates ' . self::PRODUCT . ' outside its factories: '
                    . self::PRODUCT . ' must be instantiated by ' . self::NS . 'ReflectionFactory.',
                15,
                'Move this instantiation into ' . self::NS . 'ReflectionFactory, or add ' . self::NS . 'CallableRogue to the #[ForceFactory] attribute of ' . self::PRODUCT . '.',
            ],
        ]);
    }

    // the same file holds direct calls too: only the first-class callable at the end belongs to this rule
    public function testCallableInsideFactoryIsAllowed(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Reflection/ReflectionFactory.php'], []);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../rules.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ForceFactoryReflectionCallableRule::class);
    }
}
