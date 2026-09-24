<?php

declare(strict_types=1);

namespace DemoHardening\Good;

final class NoPosixProcessControlGood
{
    public function enqueue(JobBus $bus): void
    {
        // Process identity / signals belong to the supervisor, not request code.
        $bus->dispatch(new HeavyJob());
    }
}
