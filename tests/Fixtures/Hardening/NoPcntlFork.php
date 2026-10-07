<?php

declare(strict_types=1);

namespace DemoHardening;

final class NoPcntlFork
{
    public function bad(): void
    {
        pcntl_fork(); // error
        pcntl_wait($status); // error
        pcntl_waitpid(-1, $status); // error
    }
}
