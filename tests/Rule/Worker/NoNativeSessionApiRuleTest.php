<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoNativeSessionApiRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoNativeSessionApiRule>
 */
final class NoNativeSessionApiRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoNativeSessionApiRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoNativeSessionApi.php'], [
            [
                'session_start() bypasses framework session lifecycle. Under FrankenPHP worker mode use the framework session service so state is reset between requests.',
                11,
            ],
        ]);
    }
}
