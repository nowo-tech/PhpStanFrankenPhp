<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoDateDefaultTimezoneSetRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoDateDefaultTimezoneSetRule>
 */
final class NoDateDefaultTimezoneSetRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoDateDefaultTimezoneSetRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoDateDefaultTimezoneSet.php'], [
            [
                'date_default_timezone_set() changes the process default timezone and persists on FrankenPHP workers. Configure date.timezone in php.ini / the FrankenPHP image, or pass explicit timezones to DateTime APIs.',
                11,
            ],
        ]);
    }
}
