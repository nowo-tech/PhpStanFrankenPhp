<?php

declare(strict_types=1);

namespace DemoHardening\Good;

final class NoRegisterTickFunctionGood
{
    public function instrument(Metrics $metrics): void
    {
        $metrics->increment('request');
    }
}
