<?php

declare(strict_types=1);

namespace DemoHardening;

final class NoPcntlFork
{
    public function bad(): void
    {
        pcntl_fork(); // error
    }
}
