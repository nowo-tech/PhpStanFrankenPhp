<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoPersistentIniSetGood
{
    public function configure(): void
    {
        // Configure memory_limit / timezone in php.ini or the FrankenPHP image, not via ini_set().
    }
}
