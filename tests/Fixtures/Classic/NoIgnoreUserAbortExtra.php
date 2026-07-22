<?php

declare(strict_types=1);

namespace DemoClassic;

final class NoIgnoreUserAbortExtra
{
    public function viaInt(): void
    {
        ignore_user_abort(1);
    }

    public function viaString(): void
    {
        ignore_user_abort('true');
    }

    public function disabled(): void
    {
        ignore_user_abort(false);
    }
}
