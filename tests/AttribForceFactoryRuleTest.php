<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\ForceFactoryRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @requires PHP >= 8.0
 * @extends RuleTestCase<ForceFactoryRule>
 */
class AttribForceFactoryRuleTest extends RuleTestCase
{
    private const ERROR_MESSAGE = 'Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\ForcedFactoryProduct must be instantiated by Test\Ebln\PHPStan\EnforceFactory\dataAttrib\ForcedFactory or Test\Ebln\PHPStan\EnforceFactory\dataAttrib\TraitFactory!' . "\n    💡 Only use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\ForcedFactory or Test\Ebln\PHPStan\EnforceFactory\dataAttrib\TraitFactory to create an instance of Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\ForcedFactoryProduct!";

    // Sadly this remains a vector, as phpstan fails to infer the created class name
    public function testLoopholeFactory(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/LoopholeFactory.php'], []);
    }

    // Sadly this remains a vector, as phpstan fails to infer the created class name
    public function testLoopholeInvoker(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/LoopholeFactory.php', __DIR__ . '/dataAttrib/LoopholeInvoker.php'], []);
    }

    public function testEmptyAllowedClasses(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/EmptyFactory.php'], [
            [
                'Test\Ebln\PHPStan\EnforceFactory\dataAttrib\code\EmptyProduct has either no factories defined or a conflict between interface and attribute!',
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

    public function testAllowedFactory(): void
    {
        $this->analyse([__DIR__ . '/dataAttrib/ForcedFactory.php'], []);
    }

    public function testIndependentFactory(): void
    {
        $offset = 1;
        $this->analyse([__DIR__ . '/dataAttrib/IndependentFactory.php'], [
        ]);
    }

    protected function getRule(): Rule
    {
        return new ForceFactoryRule($this->createReflectionProvider());
    }
}
