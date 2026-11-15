<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoFlushInHandWrittenTransactionRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoFlushInHandWrittenTransactionRule>
 */
final class NoFlushInHandWrittenTransactionRuleTest extends AbstractRuleTestCase
{
    private bool $enabled = true;

    protected function getRule(): Rule
    {
        return new NoFlushInHandWrittenTransactionRule($this->enabled);
    }

    public function testRule(): void
    {
        $message = static fn (string $where, string $hint = ''): string => 'EntityManager::flush() inside '.$where.' runs on a nested transaction (a savepoint): after a deadlock (MySQL/MariaDB, DBAL 4 savepoints) the savepoint is gone, the ORM\'s own rollback fails with 1305, the deadlock is never retried and the connection stays nested for the worker\'s next requests. Run the unit of work through TransactionRunnerInterface::run() (nowo-tech/transaction-contract; one flush at the outer level, connection repaired on failure), or move the flush out of the transaction'.$hint.'.';

        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoFlushInHandWrittenTransaction.php'], [
            [$message('a wrapInTransaction() callable', ' (EntityManager::wrapInTransaction() already flushes after the callable)'), 52],
            [$message('a beginTransaction()…commit() block'), 74],
            [$message('a beginTransaction()…commit() block'), 89],
            [$message('a transactional() callable'), 101],
            [$message('a beginTransaction()…commit() block'), 116],
            [$message('a beginTransaction()…commit() block'), 169],
            [$message('a beginTransaction()…commit() block'), 173],
            [$message('a beginTransaction()…commit() block'), 197],
            [$message('a beginTransaction()…commit() block'), 209],
        ]);
    }

    public function testDisabled(): void
    {
        $this->enabled = false;
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoFlushInHandWrittenTransaction.php'], []);
    }
}
