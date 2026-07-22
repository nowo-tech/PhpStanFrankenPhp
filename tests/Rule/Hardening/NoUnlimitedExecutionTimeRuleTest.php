<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Hardening;

use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoUnlimitedExecutionTimeRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoUnlimitedExecutionTimeRule>
 */
final class NoUnlimitedExecutionTimeRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoUnlimitedExecutionTimeRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Hardening/NoUnlimitedExecutionTime.php'], [
            [
                'set_time_limit(0) disables the execution deadline and can pin a FrankenPHP worker indefinitely. Use a finite limit aligned with Caddy/FrankenPHP timeouts.',
                11,
            ],
        ]);
    }
}
