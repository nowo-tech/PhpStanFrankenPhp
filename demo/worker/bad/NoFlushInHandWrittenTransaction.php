<?php

declare(strict_types=1);

namespace DemoWorker;

use Doctrine\ORM\EntityManagerInterface;

final class NoFlushInHandWrittenTransaction
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function erase(object $user): void
    {
        $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($user): void {
            $entityManager->remove($user);
            $entityManager->flush(); // error: nested flush on a savepoint
        });
    }
}
