<?php

declare(strict_types=1);

namespace DemoClassic;

final class NoIgnoreUserAbort
{
    public function bad(): void
    {
        ignore_user_abort(true); // error
    }

    public function okQuery(): int
    {
        return ignore_user_abort();
    }
}
