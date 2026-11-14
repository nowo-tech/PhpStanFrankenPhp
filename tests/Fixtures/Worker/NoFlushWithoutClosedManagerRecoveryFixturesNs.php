<?php

declare(strict_types=1);

namespace DemoWorker\Fixtures\Db;

use Doctrine\ORM\EntityManagerInterface;

final class LoadUsers
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function load(): void
    {
        $this->entityManager->flush(); // fine: fixtures namespace is skipped
    }
}
