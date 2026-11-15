<?php

declare(strict_types=1);

namespace DemoWorker\Db\Flush;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

interface TransactionRunnerInterface
{
    public function run(callable $work): mixed;
}

final class RecoveringFlusher
{
    public function flush(EntityManagerInterface $entityManager): void
    {
        $entityManager->flush();
    }
}

final class Cache
{
    public function flush(): void
    {
    }
}

// Modelled on UserKit PersonalDataEraser::erase().
final class PersonalDataEraser
{
    public function __construct(
        private ManagerRegistry $doctrine,
    ) {
    }

    /**
     * @return list<string>
     */
    public function erase(object $user): array
    {
        $entityManager = $this->doctrine->getManagerForClass($user::class);
        if (!$entityManager instanceof EntityManagerInterface) {
            return [];
        }

        return $entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager): array {
            $ran = ['a'];
            $entityManager->flush(); // error: inside wrapInTransaction()

            return $ran;
        });
    }
}

// Modelled on AuthKit InvitationAcceptor::accept() (flush through a recovering helper).
final class InvitationAcceptor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecoveringFlusher $recovery,
        private Cache $cache,
    ) {
    }

    public function accept(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        try {
            $this->recovery->flush($this->entityManager); // error: helper flush between begin and commit
            $this->cache->flush(); // fine: not an entity manager
            $connection->commit();
        } catch (Throwable $e) {
            $this->entityManager->flush(); // fine for this rule: in a catch block
            $connection->rollBack();

            throw $e;
        }
        $this->entityManager->flush(); // fine: after the commit
    }

    public function noCommit(): void
    {
        $this->entityManager->getConnection()->beginTransaction();
        $this->entityManager->flush(); // error: no commit after the begin
    }

    public function flushBeforeBegin(): void
    {
        $this->entityManager->flush(); // fine: before the begin
        $this->entityManager->beginTransaction();
        $this->entityManager->commit();
    }

    public function connectionTransactional(Connection $connection): void
    {
        $connection->transactional(fn (): mixed => $this->entityManager->flush()); // error: Connection::transactional() callable
    }

    public function nonClosure(): void
    {
        $this->entityManager->wrapInTransaction($this->work(...)); // fine: callable not inspected
        $this->entityManager->wrapInTransaction(function (): void {
            $this->entityManager->persist(new \stdClass()); // fine: no flush
        });
    }

    public function doubleWrapped(): void
    {
        $this->entityManager->wrapInTransaction(function (): void {
            $this->entityManager->beginTransaction();
            $this->entityManager->flush(); // error (reported once)
            $this->entityManager->commit();
        });
    }

    private function work(): void
    {
    }
}

// Modelled on UptimeMonitor TenantQuota::withTenantLock(): the flush is in the caller's callable (not seen).
final class TenantQuota
{
    public function withTenantLock(EntityManagerInterface $entityManager, callable $write): mixed
    {
        return $entityManager->wrapInTransaction(function () use ($entityManager, $write): mixed {
            $entityManager->lock(new \stdClass(), 4);

            return $write(1);
        });
    }
}

final class DoctrineTransactionRunner implements TransactionRunnerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function run(callable $work): mixed
    {
        $this->entityManager->beginTransaction();
        $result = $work();
        $this->entityManager->flush(); // fine: the runner owns the transaction
        $this->entityManager->commit();

        return $result;
    }
}

// Audit QB: sequential transactions, first-class callables, traits, anonymous classes.
final class AuditQbFlushCases
{
    public function __construct(
        private Connection $conn,
        private EntityManagerInterface $em,
    ) {
    }

    public function sequential(): void
    {
        $this->conn->beginTransaction();
        $this->em->flush(); // error: inside the first transaction
        $this->conn->commit();
        $this->em->flush(); // fine: between the two transactions
        $this->conn->beginTransaction();
        $this->em->flush(); // error: inside the second transaction
        $this->conn->commit();
    }

    public function firstClassCallable(): \Closure
    {
        $this->conn->beginTransaction();
        $flush = $this->em->flush(...); // fine: not a call
        $this->conn->commit();

        return $flush;
    }

    public function anonymous(): object
    {
        return new class($this->em) {
            public function __construct(
                private EntityManagerInterface $em,
            ) {
            }

            public function go(): void
            {
                $this->em->getConnection()->beginTransaction();
                $this->em->flush(); // error (reported once)
                $this->em->getConnection()->commit();
            }
        };
    }
}

trait FlushesInTransactionTrait
{
    public function save(EntityManagerInterface $em): void
    {
        $em->getConnection()->beginTransaction();
        $em->flush(); // error: trait methods are analysed
        $em->getConnection()->commit();
    }
}

final class UsesFlushesInTransactionTrait
{
    use FlushesInTransactionTrait;
}
