<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoErrorReportingMutation
{
    public function bad(): void
    {
        error_reporting(\E_ALL); // error
    }

    public function readIsAllowed(): int
    {
        return error_reporting();
    }
}
