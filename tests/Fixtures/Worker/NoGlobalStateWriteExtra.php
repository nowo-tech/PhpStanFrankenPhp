<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoGlobalStateWriteExtra
{
    public function bad(): void
    {
        $GLOBALS['x'] .= 'suffix'; // error
        $GLOBALS['n']++; // error
        ++$GLOBALS['n']; // error
        $GLOBALS['n']--; // error
        $GLOBALS['list']['k'] ??= []; // error
    }

    public function fine(): void
    {
        $value = $GLOBALS['x'] ?? null;
        $local = 0;
        ++$local;
    }
}
