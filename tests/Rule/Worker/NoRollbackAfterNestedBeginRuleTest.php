<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoRollbackAfterNestedBeginRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoRollbackAfterNestedBeginRule>
 */
final class NoRollbackAfterNestedBeginRuleTest extends AbstractRuleTestCase
{
    private bool $enabled = true;

    protected function getRule(): Rule
    {
        return new NoRollbackAfterNestedBeginRule($this->enabled);
    }

    public function testRule(): void
    {
        $message = static fn (string $after, bool $guarded = false): string => 'rollBack() in a catch/finally after '.$after.' can run on a nested transaction: after a deadlock MySQL drops every savepoint, the rollback fails with 1305 and the connection stays nested for every later request of the worker.'
            .($guarded ? ' The isTransactionActive() guard does not help: the transaction is still active at the level the failed rollback leaves.' : '')
            .' Close the connection on failure (or roll back only at nesting level 1), or run the work through a TransactionRunnerInterface.';

        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoRollbackAfterNestedBegin.php'], [
            [$message('beginTransaction()', true), 36],
            [$message('beginTransaction()', true), 72],
            [$message('an ORM flush() in the try block'), 100],
            [$message('wrapInTransaction() in the try block'), 119],
            [$message('beginTransaction() in the try block'), 130],
            [$message('beginTransaction() in the try block'), 131],
            [$message('beginTransaction()'), 251],
        ]);
    }

    public function testDisabled(): void
    {
        $this->enabled = false;
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoRollbackAfterNestedBegin.php'], []);
    }
}
