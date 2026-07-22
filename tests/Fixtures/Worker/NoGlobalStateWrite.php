<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoGlobalStateWrite
{
    public function bad(): void
    {
        global $shared; // error
        $GLOBALS['x'] = 1; // error
    }
}
