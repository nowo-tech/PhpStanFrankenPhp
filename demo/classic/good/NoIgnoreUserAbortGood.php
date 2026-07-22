<?php

declare(strict_types=1);

namespace DemoClassic\Good;

final class NoIgnoreUserAbortGood
{
    public function handle(): void
    {
        // Acknowledge quickly; do not ignore_user_abort(true). Dispatch a job instead.
    }
}
