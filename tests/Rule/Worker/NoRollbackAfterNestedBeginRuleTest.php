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
        $message = static fn (string $after, ?string $guard = null): string => 'rollBack() in a catch/finally after '.$after.' can run on a nested transaction: after a deadlock (MySQL/MariaDB, DBAL 4 savepoints) the server drops every savepoint, the rollback fails with 1305 and the connection stays nested for every later request of the worker.'
            .(null !== $guard ? ' The '.$guard.' guard does not help: the transaction is still active at the level the failed rollback leaves.' : '')
            .' Roll back to the level the transaction started at and close the connection only if that rollback throws (try { $connection->rollBack(); } catch (\Throwable) { $connection->close(); }), or run the work through TransactionRunnerInterface::run() (nowo-tech/transaction-contract).';

        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoRollbackAfterNestedBegin.php'], [
            [$message('beginTransaction()', 'isTransactionActive()'), 36],
            [$message('beginTransaction()', 'isTransactionActive()'), 72],
            [$message('an ORM flush() in the try block'), 100],
            [$message('wrapInTransaction() in the try block'), 119],
            [$message('beginTransaction() in the try block'), 130],
            [$message('beginTransaction() in the try block'), 131],
            [$message('beginTransaction()'), 251],
            [$message('beginTransaction()', 'getTransactionNestingLevel()'), 296],
            [$message('beginTransaction()', 'getTransactionNestingLevel()'), 305],
            [$message('beginTransaction()', 'getTransactionNestingLevel()'), 308],
            [$message('beginTransaction()'), 322],
            [$message('beginTransaction()'), 335],
            [$message('beginTransaction()'), 358],
            // Trait methods: one finding per using class, like PHPStan's own rules ("in context of class …").
            [$message('beginTransaction()'), 376],
            [$message('beginTransaction()'), 376],
        ]);
    }

    public function testDisabled(): void
    {
        $this->enabled = false;
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoRollbackAfterNestedBegin.php'], []);
    }
}
