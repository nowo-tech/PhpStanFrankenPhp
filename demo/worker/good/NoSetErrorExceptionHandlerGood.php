<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoSetErrorExceptionHandlerGood
{
    public function __construct(
        private readonly ErrorReporter $reporter,
    ) {
    }

    public function report(\Throwable $e): void
    {
        $this->reporter->report($e);
    }
}
