<?php

declare(strict_types=1);

namespace DemoHardening;

final class NoBlockingSleep
{
    public function bad(): void
    {
        sleep(1); // error
        usleep(1000); // error
    }
}
