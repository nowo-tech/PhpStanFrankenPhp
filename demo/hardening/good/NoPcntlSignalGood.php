<?php

declare(strict_types=1);

namespace DemoHardening\Good;

final class NoPcntlSignalGood
{
    public function offload(JobBus $bus): void
    {
        // Signal handling belongs to the supervisor / dedicated process, not request code.
        $bus->dispatch(new HeavyJob());
    }
}
