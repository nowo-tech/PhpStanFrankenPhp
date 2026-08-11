<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoSetLocale
{
    public function bad(): void
    {
        setlocale(\LC_ALL, 'en_US.UTF-8'); // error
    }

    public function readsAreAllowed(): void
    {
        setlocale(\LC_ALL, 0);
        setlocale(\LC_ALL, '0');
    }
}
