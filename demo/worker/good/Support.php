<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class JobBus
{
    public function dispatch(object $job): void
    {
    }
}

final class SendEmailJob
{
}

final class Clock
{
    public function unix(): int
    {
        return time();
    }
}

final class Registry
{
}

interface ErrorReporter
{
    public function report(\Throwable $e): void;
}
