<?php

declare(strict_types=1);

namespace DemoHardening\Good;

final class JobBus
{
    public function dispatch(object $job): void
    {
    }
}

final class HeavyJob
{
}

final class RetryJob
{
    public function __construct(public int $delaySeconds)
    {
    }
}

final class Metrics
{
    public function increment(string $name): void
    {
    }
}
