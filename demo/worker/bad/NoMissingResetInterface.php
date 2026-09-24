<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoMissingResetInterface
{
    private int $hits = 0;

    public function hit(): void
    {
        ++$this->hits; // error
    }
}
