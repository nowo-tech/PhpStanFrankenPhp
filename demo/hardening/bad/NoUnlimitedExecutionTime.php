<?php

declare(strict_types=1);

namespace DemoHardening;

final class NoUnlimitedExecutionTime
{
    public function bad(): void
    {
        set_time_limit(0); // error
    }
}
