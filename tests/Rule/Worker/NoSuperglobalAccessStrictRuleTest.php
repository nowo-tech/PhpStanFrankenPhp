<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoSuperglobalAccessRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoSuperglobalAccessRule>
 */
final class NoSuperglobalAccessStrictRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoSuperglobalAccessRule(true);
    }

    public function testStrictAlsoFlagsGet(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoSuperglobalAccess.php'], [
            [
                '$_ENV is not reset between FrankenPHP worker requests. Do not read/write request-specific or sensitive data via $_ENV; use the container / config.',
                11,
            ],
            [
                'Direct $_SESSION access bypasses framework session reset under FrankenPHP worker mode. Use the framework session API.',
                13,
            ],
            [
                'Direct access to $_GET is flagged in strict mode. Prefer the framework Request API (FrankenPHP does reset most request superglobals; this is a migration hygiene check).',
                19,
            ],
        ]);
    }
}
