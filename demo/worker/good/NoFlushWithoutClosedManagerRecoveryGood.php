<?php

declare(strict_types=1);

namespace DemoWorker\Good;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

final class NoFlushWithoutClosedManagerRecoveryGood
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ManagerRegistry $registry,
    ) {
    }

    public function markRead(object $participant): void
    {
        $this->entityManager->persist($participant);
        try {
            $this->entityManager->flush();
        } catch (Throwable $e) {
            if (!$this->entityManager->isOpen()) {
                $this->registry->resetManager();
            }

            throw $e;
        }
    }
}
