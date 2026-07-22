<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Classic;

use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoUnlimitedIoTimeoutRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoUnlimitedIoTimeoutRule>
 */
final class NoUnlimitedIoTimeoutRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoUnlimitedIoTimeoutRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Classic/NoUnlimitedIoTimeout.php'], [
            [
                'Unlimited setTimeout() leaves a FrankenPHP thread blocked if the child never ends. Set a finite timeout (seconds) and fail controlled.',
                13,
            ],
            [
                'curl timeout option set to 0/null disables the deadline and can pin a FrankenPHP thread. Use a positive CURLOPT_TIMEOUT / CURLOPT_TIMEOUT_MS.',
                18,
            ],
            [
                'proc_open() has no built-in timeout. Under FrankenPHP prefer Symfony Process with setTimeout()/setIdleTimeout(), or wrap with an explicit deadline and cleanup.',
                23,
            ],
        ]);
    }

    public function testExtraVariants(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Classic/NoUnlimitedIoTimeoutExtra.php'], [
            [
                'Unlimited setIdleTimeout() leaves a FrankenPHP thread blocked if the child never ends. Set a finite timeout (seconds) and fail controlled.',
                13,
            ],
            [
                'curl timeout option set to 0/null disables the deadline and can pin a FrankenPHP thread. Use a positive CURLOPT_TIMEOUT / CURLOPT_TIMEOUT_MS.',
                18,
            ],
        ]);
    }
}
