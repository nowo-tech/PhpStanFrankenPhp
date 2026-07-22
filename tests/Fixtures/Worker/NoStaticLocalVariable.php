<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoStaticLocalVariable
{
    public function bad(): int
    {
        static $count = 0; // error

        return ++$count;
    }
}
