<?php

declare(strict_types=1);

namespace DemoWorker;

use Symfony\Contracts\Service\ResetInterface;

final class NoMissingResetInterface
{
    private int $hits = 0;

    public function hit(): void
    {
        ++$this->hits; // error
    }
}

final class NoMissingResetInterfaceWithReset implements ResetInterface
{
    private int $hits = 0;

    public function hit(): void
    {
        ++$this->hits;
    }

    public function reset(): void
    {
        $this->hits = 0;
    }
}

final class CachedEntity
{
    private string $name = '';

    public function rename(string $name): void
    {
        $this->name = $name; // skipped by Entity suffix
    }
}

final class ConstructOnly
{
    private int $n;

    public function __construct(int $n)
    {
        $this->n = $n;
    }
}

final class AssignOpMutator
{
    private int $n = 0;

    public function bump(): void
    {
        ++$this->n; // error AssignOp
    }

    public function set(): void
    {
        $this->n = 1; // error Assign
    }
}

final class LocalNoiseOnly
{
    public function noop(): void
    {
        $x = 1;
        $y = 0;
        ++$y;
        if (true) {
            $z = 2;
        }
    }
}

abstract class AbstractLeaky
{
    private int $n = 0;

    public function bump(): void
    {
        ++$this->n; // skipped: abstract class
    }
}
