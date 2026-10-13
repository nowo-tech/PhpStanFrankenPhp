<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoMutableStaticPropertyWrites
{
    private static int $count = 0, $hits = 0; // 2 errors ($count, $hits)

    private static string $log = ''; // error

    private int $instance = 0;

    public function bad(): void
    {
        self::$log .= 'x'; // error
        self::$count += 2; // error
        ++self::$count; // error
        self::$hits++; // error
        --self::$hits; // error
        self::$hits--; // error
        $local = 1;
        self::$log = &$local; // error
    }

    public function fine(): void
    {
        $this->instance += 1;
        ++$this->instance;
        $copy = self::$count + 1;
    }
}
