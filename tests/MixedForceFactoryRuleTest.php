<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\ForceFactoryRule;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @requires PHP >= 8.0
 * @extends RuleTestCase<ForceFactoryRule>
 */
class MixedForceFactoryRuleTest extends RuleTestCase
{
    public function testAttributeFactory(): void
    {
        $this->analyse(
            [__DIR__ . '/dataMixed/AttributeFactory.php'],
            [
                ['Test\Ebln\PHPStan\EnforceFactory\dataMixed\code\MismatchedProduct has either no factories defined or a conflict between interface and attribute!', 25],
            ]
        );
    }

    public function testRogueAttributeFactory(): void
    {
        $this->analyse([__DIR__ . '/dataMixed/RogueAttributeFactory.php'], [
            ['Test\Ebln\PHPStan\EnforceFactory\dataMixed\code\AttributeProduct must be instantiated by Test\Ebln\PHPStan\EnforceFactory\dataMixed\AttributeFactory!', 14],
            ['Test\Ebln\PHPStan\EnforceFactory\dataMixed\code\MixedProduct must be instantiated by Test\Ebln\PHPStan\EnforceFactory\dataMixed\AttributeFactory or Test\Ebln\PHPStan\EnforceFactory\dataMixed\ForcedFactory!', 19],

        ]);
    }

    protected function getRule(): Rule
    {
        return new ForceFactoryRule(self::getContainer()->getByType(ReflectionProvider::class));
    }
}

