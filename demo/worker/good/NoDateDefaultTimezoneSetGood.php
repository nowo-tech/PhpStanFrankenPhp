<?php

declare(strict_types=1);

namespace DemoWorker\Good;

use DateTimeImmutable;
use DateTimeZone;

final class NoDateDefaultTimezoneSetGood
{
    public function nowIn(string $timezone): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone($timezone));
    }
}
