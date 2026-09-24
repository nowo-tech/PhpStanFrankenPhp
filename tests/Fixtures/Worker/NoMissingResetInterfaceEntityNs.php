<?php

declare(strict_types=1);

namespace DemoWorker\Entity;

final class OrderCache
{
    private int $n = 0;

    public function bump(): void
    {
        ++$this->n; // skipped by /entity/ namespace fragment
    }
}
