<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoNativeSessionApi
{
    public function bad(): void
    {
        session_start(); // error
    }
}
