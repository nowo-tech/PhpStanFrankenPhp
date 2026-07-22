<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Hardening;

use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoBlockingSleepRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoBlockingSleepRule>
 */
final class NoBlockingSleepRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoBlockingSleepRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Hardening/NoBlockingSleep.php'], [
            [
                'sleep() blocks a FrankenPHP worker thread. Avoid sleeping in request handlers; use a queue, retry with backoff outside the hot path, or an async client.',
                11,
            ],
            [
                'usleep() blocks a FrankenPHP worker thread. Avoid sleeping in request handlers; use a queue, retry with backoff outside the hot path, or an async client.',
                12,
            ],
        ]);
    }
}
