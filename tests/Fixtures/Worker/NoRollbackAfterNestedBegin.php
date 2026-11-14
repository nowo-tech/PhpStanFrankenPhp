<?php

declare(strict_types=1);

namespace DemoWorker\Db\Rollback;

use Closure;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PDO;
use RuntimeException;
use Throwable;

// Modelled on TaskBoard TaskApiTransaction::run() (2.16).
final class TaskApiTransaction
{
    /** @var mixed untyped on purpose: an event buffer, not a connection */
    private $events;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?ManagerRegistry $managerRegistry = null,
    ) {
    }

    public function run(Closure $write): mixed
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        try {
            $result = $write();
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack(); // error: begin before try, isTransactionActive() guard
            }
            $this->events?->rollBack(); // fine: untyped receiver not named like a connection
            if (!$this->entityManager->isOpen()) {
                $this->managerRegistry?->resetManager();
            }

            throw $exception;
        }

        return $result;
    }
}

// Modelled on AuthKit InvitationAcceptor::accept().
final class InvitationAcceptor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function accept(bool $claimed): ?string
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        try {
            if (!$claimed) {
                $connection->rollBack(); // fine: not in a catch / finally

                return null;
            }
            $this->entityManager->flush();
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack(); // error
            }
            if (!$exception instanceof RuntimeException) {
                throw $exception;
            }

            return null;
        }

        return 'ok';
    }
}

final class FlushInTry
{
    /** @var mixed untyped on purpose: resolved by its name */
    private $em;

    public function __construct(mixed $em)
    {
        $this->em = $em;
    }

    public function save(): void
    {
        try {
            $this->em->flush();
        } catch (Throwable $e) {
            $this->em->getConnection()->rollBack(); // error: flush in the try block

            throw $e;
        }
    }
}

final class WrapInTry
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(): void
    {
        try {
            $this->entityManager->wrapInTransaction(static fn (): bool => true);
        } finally {
            $this->entityManager->getConnection()->rollBack(); // error: wrapInTransaction in the try block
        }
    }

    public function beginInside(Connection $connection): void
    {
        try {
            $connection->beginTransaction();
            $connection->executeStatement('UPDATE t SET a = 1');
            $connection->commit();
        } catch (Throwable $e) {
            $connection->rollBack(); // error: beginTransaction in the try block
            $connection->rollBack(); // error (each rollback is reported)

            throw $e;
        }
    }
}

final class SafeRollbacks
{
    public function __construct(
        private Connection $connection,
        private PDO $pdo,
    ) {
    }

    public function closesOnFailure(): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->executeStatement('UPDATE t SET a = 1');
            $this->connection->commit();
        } catch (Throwable $e) {
            try {
                $this->connection->rollBack(); // fine: closes when the rollback fails
            } catch (Throwable) {
                $this->connection->close();
            }

            throw $e;
        }
    }

    public function closesInFinally(): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->commit();
        } catch (Throwable $e) {
            try {
                $this->connection->rollBack(); // fine: the finally closes
            } finally {
                $this->connection->close();
            }
        }
    }

    public function closeFirst(): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->close();
            $this->connection->rollBack(); // fine: after close()

            throw $e;
        }
    }

    public function levelOneOnly(): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->commit();
        } catch (Throwable $e) {
            if (2 === $this->connection->getTransactionNestingLevel()) {
                $this->connection->close();
            } elseif (1 === $this->connection->getTransactionNestingLevel()) {
                $this->connection->rollBack(); // fine: level 1 only
            } else {
                $this->connection->close();
            }

            throw $e;
        }
    }

    public function noTransaction(): void
    {
        try {
            $this->connection->executeStatement('UPDATE t SET a = 1');
        } catch (Throwable $e) {
            $this->connection->rollBack(); // fine for this rule: nothing opened in or before the try
            $later = function (): void {
                $this->connection->rollBack(); // fine: closures in a handler are not walked
            };
            $later();

            throw $e;
        }
    }

    public function pdo(): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack(); // fine: PDO has no savepoints

            throw $e;
        }
    }
}

final class ClosureBody
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function run(): Closure
    {
        return function (): void {
            $connection = $this->entityManager->getConnection();
            $connection->beginTransaction();
            try {
                $connection->commit();
            } catch (Throwable $e) {
                $connection->rollBack(); // error: begin before try, inside a closure

                throw $e;
            }
        };
    }
}
