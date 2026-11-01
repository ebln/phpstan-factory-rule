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

    private const INSTANTIATION_NS = 'Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\\';

    private const NO_FACTORIES_TIP = 'Pass at least one class name constant, e.g. #[ForceFactory(MyFactory::class)].';

    private const INVALID_TARGET_TIP = 'Only classes can be instantiated; put the attribute on the class.';

    private const NAMED_ARGUMENT_TIP = 'The rule reads positional arguments only: list every factory as a positional class name constant.';

    private const INVALID_FACTORY_TIP = 'List the classes that create the product; naming an interface or a trait allows nothing.';

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

    // PHP keeps a named argument in $factories, PHPStan drops it: report the name instead of an empty list
    public function testNamedArgumentIsReported(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/NamedOnly.php'], [
            ['#[ForceFactory] on ' . self::NS . 'NamedOnly passes the named argument factory.', 9, self::NAMED_ARGUMENT_TIP],
        ]);
    }

    // without the report, UnrelatedFactory would be dropped and every `new` in it reported as outside
    public function testNamedArgumentNextToPositionalOnes(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/NamedNextToPositional.php'], [
            ['#[ForceFactory] on ' . self::NS . 'NamedNextToPositional passes the named argument extra.', 9, self::NAMED_ARGUMENT_TIP],
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

    public function testInterfaceListedAsFactory(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/InterfaceAsFactory.php'], [
            [
                '#[ForceFactory] on ' . self::NS . 'InterfaceAsFactory names interface ' . self::NS . 'FactoryContract, which cannot be a factory.',
                9,
                self::INVALID_FACTORY_TIP,
            ],
        ]);
    }

    // one cause, one error: the interface is not also reported as failing the covariance check
    public function testInterfaceListedByAMarkedSubclass(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/InterfaceAsFactoryChild.php'], [
            [
                '#[ForceFactory] on ' . self::NS . 'InterfaceAsFactoryChild names interface ' . self::NS . 'FactoryContract, which cannot be a factory.',
                9,
                self::INVALID_FACTORY_TIP,
            ],
        ]);
    }

    public function testTraitListedAsFactory(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/TraitAsFactory.php'], [
            [
                '#[ForceFactory] on ' . self::NS . 'TraitAsFactory names trait ' . self::NS . 'FactoryHelper, which cannot be a factory.',
                9,
                self::INVALID_FACTORY_TIP,
            ],
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

    public function testChildMayNarrowTheFactories(): void
    {
        $this->analyse([
            __DIR__ . '/Fixtures/Definition/NarrowingChild.php',
            __DIR__ . '/Fixtures/Definition/SameListChild.php',
            __DIR__ . '/Fixtures/Definition/UnmarkedChild.php',
        ], []);
    }

    public function testChildMayNotWidenTheFactories(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/WideningChild.php'], [
            [
                '#[ForceFactory] on ' . self::NS . 'WideningChild names ' . self::NS . 'UnrelatedFactory, which is not a subclass of a factory of ' . self::NS . 'MarkedParent (' . self::NS . 'RealFactory).',
                9,
                self::covariantTip(self::NS . 'RealFactory'),
            ],
        ]);
    }

    public function testGrandchildIsCheckedAgainstTheNearestMarkedAncestor(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Definition/WideningGrandchild.php'], [
            [
                '#[ForceFactory] on ' . self::NS . 'WideningGrandchild names ' . self::NS . 'UnrelatedFactory, which is not a subclass of a factory of ' . self::NS . 'MarkedParent (' . self::NS . 'RealFactory).',
                9,
                self::covariantTip(self::NS . 'RealFactory'),
            ],
        ]);
    }

    // the instantiation tests must not rely on an attribute that the definition rule rejects
    public function testInstantiationFixturesAreValidDefinitions(): void
    {
        $products = self::INSTANTIATION_NS . 'code\\';
        $this->analyse(glob(__DIR__ . '/Fixtures/Instantiation/code/*.php') ?: [], [
            ['#[ForceFactory] on ' . $products . 'EmptyProduct lists no factory classes.', 9, self::NO_FACTORIES_TIP],
            [
                '#[ForceFactory] on ' . $products . 'InterfaceListedProduct names interface ' . $products . 'FactoryInterface, which cannot be a factory.',
                9,
                self::INVALID_FACTORY_TIP,
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

    private static function covariantTip(string $parentFactories): string
    {
        return 'A subclass may only narrow the factories of its parent: list ' . $parentFactories . ' or a subclass of it.';
    }
}
