<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoUmask
{
    public function bad(): void
    {
        umask(0o022); // error
    }

    public function readIsAllowed(): int
    {
        return umask();
    }
}
