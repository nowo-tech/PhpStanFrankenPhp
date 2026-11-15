<?php

declare(strict_types=1);

namespace DemoWorker\Db\Resolver;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

interface Flushable
{
    public function flush(): void;
}

// Every way DoctrineExprResolver resolves (or refuses) an entity manager; no recovery in the class.
final class ResolverCases
{
    /** @var mixed untyped on purpose */
    private $service;

    public function __construct(
        private ManagerRegistry $registry,
        private Connection $connection,
    ) {
        $this->service = null;
    }

    public function union(EntityManagerInterface|Connection $either): void
    {
        $either->flush(); // error: a member of the union is a manager
    }

    public function unresolved(?\Legacy\Orm\EntityManager $legacy, \Vendor\Thing $em, \Vendor\Thing $thing, \Doctrine\DBAL\Legacy\Connection $old): void
    {
        $legacy->flush(); // error: unloadable class named like a manager
        $em->flush(); // fine: a declared class not named like a manager wins over the variable name
        $thing->flush(); // fine: unloadable class, neutral name
        $old->flush(); // fine: a DBAL connection is not a manager
    }

    public function created(int $count): void
    {
        $em = new EntityManager($this->connection, $this->service);
        $em->flush(); // error: new EntityManager
        $a = $b = $this->registry->getManager();
        $a->flush(); // error: chained assignment
        $loop = $loop->next();
        $loop->flush(); // fine: self-referencing variable stops at the depth limit
        $count->flush(); // fine: scalar parameter
    }

    public function fallbacks(): void
    {
        $this->service->getEntityManager()->flush(); // error: well-known getter on an untyped receiver
        $this->service->other()->flush(); // fine: unknown getter
        $this->missing->flush(); // fine: undeclared property, neutral name
        DateTimeImmutable::createFromFormat('Y', '2026');
    }

    public function unloadableGetters(\Vendor\Thing $thing): void
    {
        $thing->getEntityManager()->flush(); // error: well-known getter on an unloadable class
        $thing->getManager()->flush(); // error
        $thing->getConnection()->flush(); // fine: a connection
        $thing->other()->flush(); // fine
        $thing->flush(['x']); // fine: neither the receiver nor the argument is a manager
    }
}
