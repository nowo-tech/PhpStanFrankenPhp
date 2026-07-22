<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoSetErrorExceptionHandler
{
    public function bad(): void
    {
        set_error_handler(static fn (): bool => true); // error
        set_exception_handler(static function (\Throwable $e): void {}); // error
    }
}
