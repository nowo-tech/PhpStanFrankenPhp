<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoChdirRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoChdirRule>
 */
final class NoChdirRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoChdirRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoChdir.php'], [
            [
                'chdir() changes the process working directory and persists on FrankenPHP workers, so later requests may resolve relative paths incorrectly. Use absolute paths or inject a base path from config instead.',
                11,
            ],
        ]);
    }
}
