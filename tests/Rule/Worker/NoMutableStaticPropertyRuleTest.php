<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoMutableStaticPropertyRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoMutableStaticPropertyRule>
 */
final class NoMutableStaticPropertyRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoMutableStaticPropertyRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoMutableStaticProperty.php'], [
            [
                'Mutable static property $cache persists across FrankenPHP worker requests. Use instance state with ResetInterface / kernel.reset, a request-scoped service, or a class constant for immutable values (PHP does not allow readonly static properties).',
                9,
            ],
            [
                'Assignment to a static property persists across FrankenPHP worker requests and can leak state between users. Clear via ResetInterface or avoid static mutable state.',
                13,
            ],
        ]);
    }
}
