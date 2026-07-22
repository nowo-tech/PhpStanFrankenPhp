<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoStaticLocalVariableRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoStaticLocalVariableRule>
 */
final class NoStaticLocalVariableRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoStaticLocalVariableRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoStaticLocalVariable.php'], [
            [
                'Static local variable ($count) retains values across FrankenPHP worker requests. Use a local variable, injected cache service, or ResetInterface-backed state.',
                11,
            ],
        ]);
    }
}
