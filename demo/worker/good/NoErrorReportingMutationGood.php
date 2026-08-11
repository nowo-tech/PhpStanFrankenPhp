<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoErrorReportingMutationGood
{
    public function currentLevel(): int
    {
        // Configure error_reporting in php.ini / the FrankenPHP image; reading is fine.
        return error_reporting();
    }
}
