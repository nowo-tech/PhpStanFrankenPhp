<?php

declare(strict_types=1);

namespace DemoHardening\Good;

final class NoPcntlForkGood
{
    public function offload(JobBus $bus): void
    {
        $bus->dispatch(new HeavyJob());
    }
}
