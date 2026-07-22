<?php

declare(strict_types=1);

namespace DemoHardening\Good;

final class NoBlockingSleepGood
{
    public function retryLater(JobBus $bus): void
    {
        $bus->dispatch(new RetryJob(delaySeconds: 5));
    }
}
