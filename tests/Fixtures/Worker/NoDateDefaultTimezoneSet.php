<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoDateDefaultTimezoneSet
{
    public function bad(): void
    {
        date_default_timezone_set('Europe/Madrid'); // error
    }
}
