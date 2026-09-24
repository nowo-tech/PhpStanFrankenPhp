<?php

declare(strict_types=1);

namespace DemoHardening;

final class NoPosixProcessControl
{
    public function bad(): void
    {
        posix_kill(1, SIGTERM); // error
        posix_setuid(0); // error
        posix_setgid(0); // error
    }
}
