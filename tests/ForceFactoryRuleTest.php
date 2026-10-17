<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\ForceFactoryRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends RuleTestCase<ForceFactoryRule>
 */
#[CoversClass(ForceFactoryRule::class)]
final class ForceFactoryRuleTest extends RuleTestCase
{
    private const NS = 'Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation\\';

    private const PRODUCT = self::NS . 'code\ForcedFactoryProduct';

    /** @var list<string> */
    private const FACTORIES = [self::NS . 'ForcedFactory', self::NS . 'TraitFactory'];

    public function testRogueFactory(): void
    {
        $rogue = self::NS . 'RogueFactory';
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/RogueFactory.php'], [
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 16),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 23),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 30),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 41),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 52),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 57),
            self::outside(self::NS . 'code\ExtendedProduct', self::FACTORIES, $rogue, 70, self::PRODUCT),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 96),
            self::outside(self::NS . 'code\IndependentForcedFactoryProduct', [self::NS . 'IndependentFactory'], $rogue, 101),
        ]);
    }

    public function testRogueFactoryViaTrait(): void
    {
        $rogue = self::NS . 'RogueTraitFactory';
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/RogueTraitFactory.php', __DIR__ . '/Fixtures/Instantiation/FactoryTrait.php'], [
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 13),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 20),
            self::outside(self::PRODUCT, self::FACTORIES, $rogue, 27),
        ]);
    }

    public function testAllowedFactoryViaTrait(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/TraitFactory.php', __DIR__ . '/Fixtures/Instantiation/FactoryTrait.php'], []);
    }

    // fail closed: an attribute whose list resolves to nothing forbids every instantiation
    public function testEmptyFactoryListForbidsInstantiation(): void
    {
        $product = self::NS . 'code\EmptyProduct';
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/EmptyFactory.php'], [
            [
                $product . ' cannot be instantiated: #[ForceFactory] on ' . $product . ' names no factory PHPStan can resolve.',
                13,
                'Fix the attribute on ' . $product . ': list at least one existing class name constant.',
            ],
        ]);
    }

    public function testIndependentFactoryWithCasedDuplicateName(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/IndependentFactory.php'], []);
    }

    /**
     * An unknown class in the union turns the `new` expression's type into an error type that hides the
     * product; the constant strings of the class expression still name it. The plain-string union on
     * line 22 is the documented loophole: its type is `string`, and nothing can be reported.
     */
    public function testUnknownClassInUnionDoesNotHideTheProduct(): void
    {
        $product = self::NS . 'code\LoopholeProduct';
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/LoopholeFactory.php'], [
            self::outside($product, self::FACTORIES, self::NS . 'LoopholeFactory', 14),
        ]);
    }

    public function testLoopholeInvokerAddsNothing(): void
    {
        $product = self::NS . 'code\LoopholeProduct';
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/LoopholeFactory.php', __DIR__ . '/Fixtures/Instantiation/LoopholeInvoker.php'], [
            self::outside($product, self::FACTORIES, self::NS . 'LoopholeFactory', 14),
        ]);
    }

    /**
     * Pinned loophole: `new static()` in an unmarked base class is typed as the base class, so a marked subclass
     * that inherits the static constructor can be created anywhere through it.
     */
    public function testInheritedStaticConstructorIsNotChecked(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/StaticConstructorBase.php', __DIR__ . '/Fixtures/Instantiation/StaticConstructorInvoker.php'], []);
    }

    public function testUnknownClassDoesNotAbortFile(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/UnknownClassFactory.php'], [
            self::outside(self::PRODUCT, self::FACTORIES, self::NS . 'UnknownClassFactory', 18),
        ]);
    }

    public function testTypedVariables(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/TypedVariableFactory.php'], [
            self::outside(self::PRODUCT, self::FACTORIES, self::NS . 'TypedVariableFactory', 13),
            self::outside(self::PRODUCT, self::FACTORIES, self::NS . 'TypedVariableFactory', 19),
        ]);
    }

    public function testParentFactoryCannotBuildMarkedChild(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/ForcedFactory.php'], [
            self::outside(self::NS . 'code\MarkedChildProduct', [self::NS . 'MarkedChildFactory'], self::NS . 'ForcedFactory', 113),
        ]);
    }

    public function testMarkedChildFactoryIsAllowed(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/MarkedChildFactory.php'], []);
    }

    // adding RogueChildFactory to the child's attribute would fail the covariance check, so the tip only says "move"
    public function testTipOmitsAFactoryTheParentForbids(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/RogueChildFactory.php'], [
            self::outside(self::NS . 'code\MarkedChildProduct', [self::NS . 'MarkedChildFactory'], null, 13),
        ]);
    }

    public function testInterfaceIsNotAFactory(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/InterfaceFactory.php'], [
            self::outside(self::NS . 'code\InterfaceListedProduct', [self::NS . 'code\FactoryInterface'], self::NS . 'InterfaceFactory', 14),
        ]);
    }

    public function testClosuresCountAsInsideTheFactory(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/ClosureFactory.php'], []);
    }

    public function testAnonymousClassInsideFactoryIsOutside(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/AnonymousInsideFactory.php'], [
            self::outside(self::NS . 'code\AnonymousProduct', [self::NS . 'AnonymousInsideFactory'], null, 16),
        ]);
    }

    public function testSubclassOfFactoryIsOutside(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/SubclassOfFactory.php'], [
            self::outside(self::PRODUCT, self::FACTORIES, self::NS . 'SubclassOfFactory', 13),
        ]);
    }

    public function testEnumCanBeAFactory(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/EnumFactory.php'], []);
    }

    public function testProductCannotCreateItself(): void
    {
        $product = self::NS . 'code\SelfCreatingProduct';
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/code/SelfCreatingProduct.php'], [
            self::outside($product, [self::NS . 'ForcedFactory'], $product, 15),
            self::outside($product, [self::NS . 'ForcedFactory'], $product, 20),
        ]);
    }

    public function testRogueFunction(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/rogue_function.php'], [
            self::outside(self::PRODUCT, self::FACTORIES, null, 11),
        ]);
    }

    public function testRogueTopLevelCode(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/rogue_toplevel.php'], [
            self::outside(self::PRODUCT, self::FACTORIES, null, 9),
        ]);
    }

    /**
     * Pinned behaviour: PHPStan analyses parameter defaults and attribute arguments of a method in the
     * scope of the declaring class, so a listed factory may use `new` there.
     */
    public function testDefaultAndAttributeArgumentsOfAFactoryMethod(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/Instantiation/DefaultAndAttributeArgs.php'], []);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../rules.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ForceFactoryRule::class);
    }

    /**
     * @param list<string> $factories
     * @param null|string  $holder    the class carrying the attribute, when it is not the product itself
     *
     * @return array{string, int, string}
     */
    private static function outside(string $product, array $factories, ?string $currentClass, int $line, ?string $holder = null): array
    {
        $list = implode(' or ', $factories);
        $tip  = 'Move this instantiation into ' . $list
            . (null === $currentClass ? '.' : ', or add ' . $currentClass . ' to the #[ForceFactory] attribute of ' . ($holder ?? $product) . '.');

        return [$product . ' must be instantiated by ' . $list . '.', $line, $tip];
    }
}
