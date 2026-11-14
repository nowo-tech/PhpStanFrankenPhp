<?php

declare(strict_types=1);

namespace DemoWorker\Good;

use Doctrine\ORM\EntityManagerInterface;

final class NoFlushInHandWrittenTransactionGood
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function erase(object $user): void
    {
        // wrapInTransaction() flushes once, at the outer level, after the callable.
        $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($user): void {
            $entityManager->remove($user);
        });
    }
}
