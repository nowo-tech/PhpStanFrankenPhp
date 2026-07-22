<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Classic;

use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoPutenvRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoPutenvRule>
 */
final class NoPutenvRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoPutenvRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Classic/NoPutenv.php'], [
            [
                'putenv() mutates the process environment and persists across requests under FrankenPHP. Configure environment via .env / container parameters instead.',
                11,
            ],
        ]);
    }
}
