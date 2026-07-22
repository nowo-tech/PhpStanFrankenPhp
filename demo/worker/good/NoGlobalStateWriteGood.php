<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoGlobalStateWriteGood
{
    public function __construct(
        private readonly Clock $clock,
    ) {
    }

    public function now(): int
    {
        return $this->clock->unix();
    }
}
