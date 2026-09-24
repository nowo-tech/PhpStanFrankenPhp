<?php

declare(strict_types=1);

namespace DemoWorker\Good;

use Symfony\Contracts\Service\ResetInterface;

final class NoMissingResetInterfaceGood implements ResetInterface
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
