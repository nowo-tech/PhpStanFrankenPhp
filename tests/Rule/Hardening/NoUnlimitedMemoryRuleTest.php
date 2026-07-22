<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Hardening;

use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoUnlimitedMemoryRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoUnlimitedMemoryRule>
 */
final class NoUnlimitedMemoryRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoUnlimitedMemoryRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Hardening/NoUnlimitedMemory.php'], [
            [
                "ini_set('memory_limit', -1) removes the memory ceiling. On FrankenPHP workers leaks accumulate until OOM. Set a finite limit and fix leaks / use max_requests.",
                11,
            ],
        ]);
    }

    public function testExtraVariants(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Hardening/NoUnlimitedMemoryExtra.php'], [
            [
                "ini_set('memory_limit', -1) removes the memory ceiling. On FrankenPHP workers leaks accumulate until OOM. Set a finite limit and fix leaks / use max_requests.",
                11,
            ],
            [
                "ini_set('memory_limit', -1) removes the memory ceiling. On FrankenPHP workers leaks accumulate until OOM. Set a finite limit and fix leaks / use max_requests.",
                16,
            ],
        ]);
    }
}
