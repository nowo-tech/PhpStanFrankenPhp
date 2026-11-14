<?php

declare(strict_types=1);

namespace DemoWorker\Db\Recovery;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

final class RecoveringFlusher
{
    public function flush(EntityManagerInterface $entityManager): void
    {
        if ($entityManager->isOpen()) {
            $entityManager->flush();
        }
    }

    public function forget(): void
    {
    }
}

final class Conversation
{
    public function isOpen(): bool
    {
        return true;
    }

    public function reopen(): void
    {
    }
}

final class EntityManagerRecovery
{
    public static function resetIfClosed(EntityManagerInterface $entityManager, ?ManagerRegistry $registry = null): void
    {
        if (!$entityManager->isOpen()) {
            $registry?->resetManager();
        }
    }

    public static function recoverLater(string $when): void
    {
    }
}

final class StaticRecovery
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(): void
    {
        try {
            $this->entityManager->flush();
        } finally {
            EntityManagerRecovery::resetIfClosed($this->entityManager);
        }
    }
}

// Modelled on ChatKit ChatService: raw flushes, no isOpen(), no registry reset.
final class ChatService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function edit(): void
    {
        $this->entityManager->flush(); // error
    }

    public function publish(Conversation $conversation): void
    {
        $this->entityManager->flush(); // error
        if (!$conversation->isOpen()) {
            $conversation->reopen(); // domain methods are not a recovery
        }
        EntityManagerRecovery::recoverLater('later');
        $this->entityManager->flush(); // error
    }
}

final class ChecksIsOpen
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(): void
    {
        if (!$this->entityManager->isOpen()) {
            return;
        }
        $this->entityManager->flush();
    }
}

final class ResetsThroughRegistry
{
    public function __construct(
        private ManagerRegistry $registry,
    ) {
    }

    public function save(): void
    {
        $em = $this->registry->getManager();
        try {
            $em->flush();
        } catch (\Throwable $e) {
            $this->registry->resetManager();

            throw $e;
        }
    }
}

final class FlushesThroughHelper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecoveringFlusher $flusher,
    ) {
    }

    public function save(): void
    {
        $this->flusher->flush($this->entityManager);
    }
}

final class UsesRecoveringDependency
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecoveringFlusher $flusher,
    ) {
    }

    public function save(): void
    {
        $this->entityManager->flush();
        $this->flusher->forget();
    }
}

final class RegistryManager
{
    public function __construct(
        private ManagerRegistry $registry,
        private Connection $connection,
    ) {
    }

    public function save(): void
    {
        $this->registry->getManager()->flush(); // error: ObjectManager from the registry
        $this->connection->close();
    }
}

final class ImportCommand
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function run(): void
    {
        $this->entityManager->flush(); // fine: console commands are skipped
    }
}

final class ConsumesFlushedObject
{
    public function save(object $other): void
    {
        $anonymous = new class {
            public function flush(): void
            {
            }
        };
        $anonymous->flush(); // fine: not an entity manager
        $other->flush(); // fine: untyped object with a neutral name
    }
}
