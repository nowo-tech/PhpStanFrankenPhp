<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoChdir
{
    public function bad(): void
    {
        chdir('/tmp'); // error
    }
}
