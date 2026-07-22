<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Hardening;

use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoPcntlForkRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoPcntlForkRule>
 */
final class NoPcntlForkRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoPcntlForkRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Hardening/NoPcntlFork.php'], [
            [
                'pcntl_fork() is unsafe under FrankenPHP (threaded SAPI). Run isolated work in a separate process/container or a queue worker, not via fork from the request thread.',
                11,
            ],
        ]);
    }
}
