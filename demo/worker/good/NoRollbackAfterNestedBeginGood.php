<?php

declare(strict_types=1);

namespace DemoWorker\Good;

use Closure;
use Doctrine\DBAL\Connection;
use Throwable;

final class NoRollbackAfterNestedBeginGood
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function run(Closure $write): mixed
    {
        $this->connection->beginTransaction();
        try {
            $result = $write();
            $this->connection->commit();
        } catch (Throwable $e) {
            try {
                $this->connection->rollBack();
            } catch (Throwable) {
                // The savepoint is gone (deadlock): close() resets the nesting level for the next request.
                $this->connection->close();
            }

            throw $e;
        }

        return $result;
    }
}
