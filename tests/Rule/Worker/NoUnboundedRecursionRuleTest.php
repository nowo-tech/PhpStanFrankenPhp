<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoUnboundedRecursionRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoUnboundedRecursionRule>
 */
final class NoUnboundedRecursionRuleTest extends AbstractRuleTestCase
{
    private bool $enabled = true;

    protected function getRule(): Rule
    {
        return new NoUnboundedRecursionRule($this->enabled);
    }

    public function testRule(): void
    {
        $message = static fn (string $subject, string $relation): string => $subject.' calls itself while walking '.$relation.' with no depth limit and no visited set: a cycle in the data or a very deep tree recurses until the worker hits the memory limit or overflows the stack, and the crash takes down every request of that worker. Pass a depth counter (stop at a maximum) or a visited set, or walk iteratively with an explicit stack.';

        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoUnboundedRecursion.php'], [
            [$message('Method path()', '->parent'), 34],
            [$message('Method count()', '->children'), 41],
            [$message('Method descendantsOf()', 'childrenOf()'), 75],
            [$message('Method rootOf()', 'getParent()'), 119],
            [$message('Closure $walk in flatten()', 'getReplies()'), 144],
            [$message('Method rootId()', 'getParent()'), 183],
            [$message('Method size()', 'getChildren()'), 198],
        ]);
    }

    public function testDisabled(): void
    {
        $this->enabled = false;
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoUnboundedRecursion.php'], []);
    }
}
