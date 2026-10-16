<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\ForceFactoryDefinitionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends RuleTestCase<ForceFactoryDefinitionRule>
 */
#[CoversClass(ForceFactoryDefinitionRule::class)]
final class ForceFactoryDefinitionRuleTest extends RuleTestCase
{
    private const NS = 'Test\Ebln\PHPStan\EnforceFactory\Fixtures\Definition\\';

    private const NO_FACTORIES_TIP = 'Pass at least one class name constant, e.g. #[ForceFactory(MyFactory::class)].';

    private const INVALID_TARGET_TIP = 'Only classes can be instantiated; put the attribute on the class.';

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../rules.neon'];
    }

    public function testEmptyList(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/EmptyList.php'], [
            ['#[ForceFactory] on ' . self::NS . 'EmptyList lists no factory classes.', 9, self::NO_FACTORIES_TIP],
        ]);
    }

    public function testNamedArgumentDoesNotCount(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/NamedOnly.php'], [
            ['#[ForceFactory] on ' . self::NS . 'NamedOnly lists no factory classes.', 9, self::NO_FACTORIES_TIP],
        ]);
    }

    public function testNonConstantStringDoesNotCount(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/NonConstant.php'], [
            ['#[ForceFactory] on ' . self::NS . 'NonConstant lists no factory classes.', 9, self::NO_FACTORIES_TIP],
        ]);
    }

    // PHP resolves attribute names case-insensitively; PHPStan keeps the written case when there are no arguments
    public function testMisCasedAttributeWithoutArguments(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/MisCasedEmpty.php'], [
            ['#[ForceFactory] on ' . self::NS . 'MisCasedEmpty lists no factory classes.', 7, self::NO_FACTORIES_TIP],
        ]);
    }

    public function testUnknownFactory(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/UnknownFactory.php'], [
            ['#[ForceFactory] on ' . self::NS . 'UnknownFactory names unknown class Nope\Missing.', 9],
        ]);
    }

    public function testOnInterface(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/OnInterface.php'], [
            ['#[ForceFactory] is not allowed on interface ' . self::NS . 'OnInterface.', 9, self::INVALID_TARGET_TIP],
        ]);
    }

    public function testOnTrait(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/OnTrait.php'], [
            ['#[ForceFactory] is not allowed on trait ' . self::NS . 'OnTrait.', 9, self::INVALID_TARGET_TIP],
        ]);
    }

    public function testOnEnum(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/OnEnum.php'], [
            ['#[ForceFactory] is not allowed on enum ' . self::NS . 'OnEnum.', 9, self::INVALID_TARGET_TIP],
        ]);
    }

    public function testLegacyInterface(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/Legacy.php'], [
            [
                self::NS . 'Legacy implements the removed Ebln\PHPStan\EnforceFactory\ForceFactoryInterface.',
                9,
                'Replace the interface with the #[ForceFactory] attribute.',
            ],
        ]);
    }

    public function testLegacyInterfaceOnAnonymousClass(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/LegacyAnonymous.php'], [
            [
                'Anonymous class implements the removed Ebln\PHPStan\EnforceFactory\ForceFactoryInterface.',
                13,
                'Replace the interface with the #[ForceFactory] attribute.',
            ],
        ]);
    }

    public function testValid(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/Valid.php'], []);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ForceFactoryDefinitionRule::class);
    }
}
