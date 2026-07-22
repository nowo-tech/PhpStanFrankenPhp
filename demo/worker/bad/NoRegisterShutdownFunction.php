<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoRegisterShutdownFunction
{
    public function bad(): void
    {
        register_shutdown_function(static function (): void {}); // error
    }
}
