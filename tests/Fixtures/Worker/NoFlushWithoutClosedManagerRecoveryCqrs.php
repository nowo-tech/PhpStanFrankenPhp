<?php

declare(strict_types=1);

namespace DemoWorker\Application\Command;

use Doctrine\ORM\EntityManagerInterface;

// Audit QB-L4: a DDD / CQRS command handler runs in the worker (Messenger); its namespace is not a console command.
final class CreateTaskHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(object $task): void
    {
        $this->entityManager->persist($task);
        $this->entityManager->flush(); // error
    }
}
