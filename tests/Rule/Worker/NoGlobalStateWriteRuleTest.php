<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoGlobalStateWriteRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoGlobalStateWriteRule>
 */
final class NoGlobalStateWriteRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoGlobalStateWriteRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoGlobalStateWrite.php'], [
            [
                'The global keyword shares mutable state across FrankenPHP worker requests. Pass dependencies explicitly or use the service container.',
                11,
            ],
            [
                'Writing to $GLOBALS persists across FrankenPHP worker requests. Prefer dependency injection or request attributes.',
                12,
            ],
        ]);
    }
}
