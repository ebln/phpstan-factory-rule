<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\ForceFactoryRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForceFactoryRule>
 */
class ForceFactoryRuleTest extends RuleTestCase
{
    private const ERROR_MESSAGE = 'Test\Ebln\PHPStan\EnforceFactory\data\code\ForcedFactoryProduct must be instantiated by Test\Ebln\PHPStan\EnforceFactory\data\ForcedFactory or Test\Ebln\PHPStan\EnforceFactory\data\TraitFactory!';

    // Sadly this remains a vector, as phpstan fails to infer the created class name
    public function testLoopholeFactory(): void
    {
        $this->analyse([__DIR__ . '/data/LoopholeFactory.php'], []);
    }

    public function testEmptyAllowedClasses(): void
    {
        $this->analyse([__DIR__ . '/data/EmptyFactory.php'], [
            [
                'Test\Ebln\PHPStan\EnforceFactory\data\code\EmptyProduct cannot be instantiated by other classes; see Ebln\PHPStan\EnforceFactory\ForceFactoryInterface',
                13,
            ],
        ]);
    }

    public function testRogueFactory(): void
    {
        $this->analyse([__DIR__ . '/data/RogueFactory.php'], [
            [self::ERROR_MESSAGE, 15],
            [self::ERROR_MESSAGE, 22],
            [self::ERROR_MESSAGE, 29],
            [self::ERROR_MESSAGE, 40],
            [self::ERROR_MESSAGE, 40],
            [self::ERROR_MESSAGE, 51],
            [self::ERROR_MESSAGE, 56],
            ['Test\Ebln\PHPStan\EnforceFactory\data\code\ExtendedProduct must be instantiated by Test\Ebln\PHPStan\EnforceFactory\data\ForcedFactory or Test\Ebln\PHPStan\EnforceFactory\data\TraitFactory!', 69],
        ]);
    }

    public function testRogueFactoryAndTrait(): void
    {
        $this->analyse([__DIR__ . '/data/RogueTraitFactory.php', __DIR__ . '/data/FactoryTrait.php'], [
            [self::ERROR_MESSAGE, 13],
            [self::ERROR_MESSAGE, 20],
            [self::ERROR_MESSAGE, 27],
        ]);
    }

    public function testTraitedFactory(): void
    {
        $this->analyse([__DIR__ . '/data/TraitFactory.php', __DIR__ . '/data/FactoryTrait.php'], []);
    }

    public function testAllowedFactory(): void
    {
        $this->analyse([__DIR__ . '/data/ForcedFactory.php'], []);
    }

    protected function getRule(): Rule
    {
        return new ForceFactoryRule();
    }
}
