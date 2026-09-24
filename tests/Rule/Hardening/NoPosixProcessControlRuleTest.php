<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Hardening;

use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoPosixProcessControlRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoPosixProcessControlRule>
 */
final class NoPosixProcessControlRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoPosixProcessControlRule();
    }

    public function testRule(): void
    {
        $suffix = ' mutates OS process identity or signals and is unsafe under FrankenPHP workers (especially with FRANKENPHP_RESET_KERNEL unset/false). Handle process control in the supervisor / a dedicated CLI process.';

        $this->analyse([__DIR__.'/../../Fixtures/Hardening/NoPosixProcessControl.php'], [
            ['posix_kill()'.$suffix, 11],
            ['posix_setuid()'.$suffix, 12],
            ['posix_setgid()'.$suffix, 13],
        ]);
    }
}
