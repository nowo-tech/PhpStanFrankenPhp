<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Hardening;

use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoPcntlSignalRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoPcntlSignalRule>
 */
final class NoPcntlSignalRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoPcntlSignalRule();
    }

    public function testRule(): void
    {
        $msg = ' is unsafe under FrankenPHP (threaded SAPI). Handle signals outside the request thread (supervisor / dedicated process), not from application request code.';

        $this->analyse([__DIR__.'/../../Fixtures/Hardening/NoPcntlSignal.php'], [
            ['pcntl_signal()'.$msg, 11],
            ['pcntl_async_signals()'.$msg, 12],
            ['pcntl_signal_dispatch()'.$msg, 13],
            ['pcntl_signal_get_handler()'.$msg, 14],
            ['pcntl_sigprocmask()'.$msg, 15],
            ['pcntl_sigwaitinfo()'.$msg, 16],
            ['pcntl_sigtimedwait()'.$msg, 17],
            ['pcntl_alarm()'.$msg, 18],
        ]);
    }
}
