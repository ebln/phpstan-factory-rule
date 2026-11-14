<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\ForceFactoryReflectionRule;
use Ebln\PHPStan\EnforceFactory\InstantiationChecker;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends RuleTestCase<ForceFactoryReflectionRule>
 */
#[CoversClass(ForceFactoryReflectionRule::class)]
#[CoversClass(InstantiationChecker::class)]
final class ForceFactoryReflectionRuleTest extends RuleTestCase
{
    private const NS = 'Test\Ebln\PHPStan\EnforceFactory\Fixtures\Reflection\\';

    private const PRODUCT = self::NS . 'code\ReflectedProduct';

    private const OTHER_PRODUCT = self::NS . 'code\OtherReflectedProduct';

    /** @var list<string> */
    private const FACTORIES = [self::NS . 'ReflectionFactory'];

    public function testRogueReflection(): void
    {
        $rogue = self::NS . 'RogueReflection';
        $this->analyse([__DIR__ . '/Fixtures/Reflection/RogueReflection.php'], [
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 'newInstance', 14),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 'newInstanceArgs', 19),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 'newInstanceWithoutConstructor', 24),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 'newInstance', 29), // miscased call, canonical name in the message
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 'newInstance', 35), // nullsafe call on a nullable caller
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 'newInstance', 40), // ReflectionObject, message still names ReflectionClass
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 'newInstance', 46),
            self::outside(self::OTHER_PRODUCT, self::FACTORIES, $rogue, 'newInstance', 46),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 'newInstance', 52), // plain call on a nullable caller
        ]);
    }

    // the callable rule reports $reflection->newInstance(...); this rule must not report it a second time
    public function testFirstClassCallableIsLeftToTheCallableRule(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Reflection/CallableRogue.php'], []);
    }

    public function testListedFactoryMayReflect(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Reflection/ReflectionFactory.php'], []);
    }

    // fail closed: an attribute whose list resolves to nothing forbids every instantiation
    public function testEmptyFactoryListForbidsReflection(): void
    {
        $product = self::NS . 'code\EmptyReflectedProduct';
        $this->analyse([__DIR__ . '/Fixtures/Reflection/EmptyReflection.php'], [
            [
                $product . ' cannot be instantiated: #[ForceFactory] on ' . $product . ' names no factory PHPStan can resolve.',
                13,
                'Fix the attribute on ' . $product . ': list at least one existing class name constant.',
            ],
        ]);
    }

    public function testAnonymousClassInsideFactoryIsOutside(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Reflection/AnonymousReflectionFactory.php'], [
            self::outside(self::NS . 'code\AnonymousReflectedProduct', [self::NS . 'AnonymousReflectionFactory'], null, 'newInstance', 16),
        ]);
    }

    public function testSubclassOfFactoryIsOutside(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Reflection/SubclassReflectionFactory.php'], [
            self::outside(self::PRODUCT, self::FACTORIES, self::NS . 'SubclassReflectionFactory', 'newInstance', 13),
        ]);
    }

    /**
     * Skipped on purpose: a plain ReflectionClass, a class name from a string, an unmarked product,
     * a method name from a variable (documented loophole), another object and other methods.
     */
    public function testUnrelatedCallsAreNotReported(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Reflection/NotReflecting.php'], []);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../rules.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ForceFactoryReflectionRule::class);
    }

    /**
     * @param list<string> $factories
     *
     * @return array{string, int, string}
     */
    private static function outside(string $product, array $factories, ?string $currentClass, string $method, int $line): array
    {
        $list = implode(' or ', $factories);
        $tip  = 'Move this instantiation into ' . $list
            . (null === $currentClass ? '.' : ', or add ' . $currentClass . ' to the #[ForceFactory] attribute of ' . $product . '.');

        return [
            sprintf('ReflectionClass::%s() creates %s outside its factories: %s must be instantiated by %s.', $method, $product, $product, $list),
            $line,
            $tip,
        ];
    }
}
