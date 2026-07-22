<?php

declare(strict_types=1);

namespace DemoHardening\Good;

final class NoUnlimitedExecutionTimeGood
{
    public function configure(): void
    {
        set_time_limit(30);
    }
}
