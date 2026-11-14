<?php

declare(strict_types=1);

namespace DemoWorker;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

final class NoRollbackAfterNestedBegin
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function run(Closure $write): mixed
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        try {
            $result = $write();
            $this->entityManager->flush();
            $connection->commit();
        } catch (Throwable $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack(); // error: 1305 after a deadlock, connection stays nested
            }

            throw $e;
        }

        return $result;
    }
}
