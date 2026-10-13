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

    public function testCompoundAssignmentsAndIncrementsAreReported(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoGlobalStateWriteExtra.php'], [
            [
                'Writing to $GLOBALS persists across FrankenPHP worker requests. Prefer dependency injection or request attributes.',
                11,
            ],
            [
                'Writing to $GLOBALS persists across FrankenPHP worker requests. Prefer dependency injection or request attributes.',
                12,
            ],
            [
                'Writing to $GLOBALS persists across FrankenPHP worker requests. Prefer dependency injection or request attributes.',
                13,
            ],
            [
                'Writing to $GLOBALS persists across FrankenPHP worker requests. Prefer dependency injection or request attributes.',
                14,
            ],
            [
                'Writing to $GLOBALS persists across FrankenPHP worker requests. Prefer dependency injection or request attributes.',
                15,
            ],
        ]);
    }
}
