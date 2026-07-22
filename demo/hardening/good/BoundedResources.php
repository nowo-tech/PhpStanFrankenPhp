<?php

declare(strict_types=1);

namespace DemoHardening\Good;

/** Overview sample: finite deadlines and queue offload. */
final class BoundedResources
{
    public function configure(): void
    {
        set_time_limit(30);
    }

    public function offload(JobBus $bus): void
    {
        $bus->dispatch(new HeavyJob());
    }
}
