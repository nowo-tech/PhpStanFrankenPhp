<?php

declare(strict_types=1);

namespace DemoWorker;

use Doctrine\ORM\EntityManagerInterface;

final class NoFlushWithoutClosedManagerRecovery
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function markRead(object $participant): void
    {
        $this->entityManager->persist($participant);
        $this->entityManager->flush(); // error (opt-in rule): a failed flush leaves the manager closed
    }
}
