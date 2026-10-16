<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\AllowedFactoriesResolver;
use Ebln\PHPStan\EnforceFactory\ForceFactoryRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForceFactoryRule>
 */
class ForceFactoryRuleTest extends RuleTestCase
{
    private const ERROR_MESSAGE = 'Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\ForcedFactoryProduct must be instantiated by Test\Ebln\PHPStan\EnforceFactory\dataAttrib\ForcedFactory or Test\Ebln\PHPStan\EnforceFactory\dataAttrib\TraitFactory!' . "\n    💡 Only use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\ForcedFactory or Test\Ebln\PHPStan\EnforceFactory\dataAttrib\TraitFactory to create an instance of Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\ForcedFactoryProduct!";

    // Since PHPStan 2.3 the concatenated class name is inferred as constant strings, so the former loophole is reported
    // Documented loophole: the class expression's type collapses to `object` once a non-constant string joins the union
    public function testLoopholeFactory(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/LoopholeFactory.php'], []);
    }

    public function testLoopholeInvoker(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/LoopholeFactory.php', __DIR__ . '/dataAttrib/LoopholeInvoker.php'], []);
    }

    public function testEmptyAllowedClasses(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/EmptyFactory.php'], [
            [
                'Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\EmptyProduct has no factories defined!',
                13,
            ],
        ]);
    }

    public function testRogueFactory(): void
    {
        $offset = 1;
        $this->analyse([__DIR__ . '/dataAttrib/RogueFactory.php'], [
            [self::ERROR_MESSAGE, 15 + $offset],
            [self::ERROR_MESSAGE, 22 + $offset],
            [self::ERROR_MESSAGE, 29 + $offset],
            [self::ERROR_MESSAGE, 40 + $offset],
            [self::ERROR_MESSAGE, 51 + $offset],
            [self::ERROR_MESSAGE, 56 + $offset],
            ['Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\ExtendedProduct must be instantiated by Test\Ebln\PHPStan\EnforceFactory\dataAttrib\ForcedFactory or Test\Ebln\PHPStan\EnforceFactory\dataAttrib\TraitFactory!' . "\n    💡 Only use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\ForcedFactory or Test\Ebln\PHPStan\EnforceFactory\dataAttrib\TraitFactory to create an instance of Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\ExtendedProduct!", 69 + $offset],
            [self::ERROR_MESSAGE, 95 + $offset],
            ['Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\IndependentForcedFactoryProduct must be instantiated by Test\Ebln\PHPStan\EnforceFactory\dataAttrib\IndependentFactory!' . "\n    💡 Only use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\IndependentFactory to create an instance of Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\IndependentForcedFactoryProduct!", 100 + $offset],
        ]);
    }

    public function testRogueFactoryAndTrait(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/RogueTraitFactory.php', __DIR__ . '/dataAttrib/FactoryTrait.php'], [
            [self::ERROR_MESSAGE, 13],
            [self::ERROR_MESSAGE, 20],
            [self::ERROR_MESSAGE, 27],
        ]);
    }

    public function testTraitedFactory(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/TraitFactory.php', __DIR__ . '/dataAttrib/FactoryTrait.php'], []);
    }

    public function testIndependentFactory(): void
    {
        $offset = 1;
        $this->analyse([__DIR__ . '/dataAttrib/IndependentFactory.php'], [
        ]);
    }

    public function testUnknownClassDoesNotAbortFile(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/UnknownClassFactory.php'], [
            [self::ERROR_MESSAGE, 18],
        ]);
    }

    public function testTypedVariables(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/TypedVariableFactory.php'], [
            [self::ERROR_MESSAGE, 13],
            [self::ERROR_MESSAGE, 19],
        ]);
    }

    public function testParentFactoryCannotBuildMarkedChild(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/ForcedFactory.php'], [
            [
                'Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\MarkedChildProduct must be instantiated by Test\Ebln\PHPStan\EnforceFactory\dataAttrib\MarkedChildFactory!',
                113,
                'Only use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\MarkedChildFactory to create an instance of Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\MarkedChildProduct!',
            ],
        ]);
    }

    public function testMarkedChildFactoryIsAllowed(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/MarkedChildFactory.php'], []);
    }

    public function testInterfaceIsNotAFactory(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/InterfaceFactory.php'], [
            [
                'Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\InterfaceListedProduct must be instantiated by Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\FactoryInterface!',
                14,
                'Only use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\FactoryInterface to create an instance of Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\InterfaceListedProduct!',
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new ForceFactoryRule(new AllowedFactoriesResolver(self::createReflectionProvider()));
    }
}
