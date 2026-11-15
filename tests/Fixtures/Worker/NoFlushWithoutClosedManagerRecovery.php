<?php

declare(strict_types=1);

namespace DemoWorker\Db\Recovery;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

final class ImportCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct('app:import');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->entityManager->flush(); // fine: Symfony Console commands are skipped

        return 0;
    }
}

#[AsCommand(name: 'app:purge')]
final class PurgeInvokable
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(): int
    {
        $this->entityManager->flush(); // fine: #[AsCommand] invokable command

        return 0;
    }
}

final class ReturnsFlushCallable
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function later(): \Closure
    {
        return $this->entityManager->flush(...); // fine: a first-class callable is not a flush
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
        $flusher = new class($this->manager()) {
            public function __construct(
                private EntityManagerInterface $entityManager,
            ) {
            }

            public function go(): void
            {
                $this->entityManager->flush(); // fine: anonymous classes are skipped, and not counted for the outer class
            }
        };
        $flusher->go();
    }

    private function manager(): EntityManagerInterface
    {
        throw new \LogicException();
    }
}

final class UserFixture
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function load(): void
    {
        $this->entityManager->flush(); // fine: fixtures are skipped
    }
}
