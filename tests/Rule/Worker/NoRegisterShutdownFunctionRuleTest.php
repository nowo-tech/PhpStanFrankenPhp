<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoRegisterShutdownFunctionRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoRegisterShutdownFunctionRule>
 */
final class NoRegisterShutdownFunctionRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoRegisterShutdownFunctionRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoRegisterShutdownFunction.php'], [
            [
                'register_shutdown_function() does not run after each FrankenPHP worker request; it runs when the worker script ends. Use framework terminate events, Messenger, or a queue for post-response work.',
                11,
            ],
        ]);
    }
}
