<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Hardening;

use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoRegisterTickFunctionRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoRegisterTickFunctionRule>
 */
final class NoRegisterTickFunctionRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoRegisterTickFunctionRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Hardening/NoRegisterTickFunction.php'], [
            [
                'register_tick_function() stays active on the FrankenPHP worker and is rarely safe in request code. Prefer explicit instrumentation or middleware.',
                11,
            ],
        ]);
    }
}
