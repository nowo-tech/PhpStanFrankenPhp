<?php

declare(strict_types=1);

namespace DemoHardening\Good;

final class NoUnlimitedMemoryGood
{
    public function configure(): void
    {
        // Set a finite memory_limit in php.ini / FrankenPHP image (e.g. 256M).
    }
}
