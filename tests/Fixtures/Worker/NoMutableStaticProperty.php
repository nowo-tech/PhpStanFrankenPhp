<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoMutableStaticProperty
{
    private static array $cache = []; // error

    public function bad(): void
    {
        self::$cache['x'] = 1; // error
    }
}
