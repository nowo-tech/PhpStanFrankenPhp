<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoPersistentIniAlter
{
    public function bad(string $key): void
    {
        ini_alter('memory_limit', '256M'); // error
        ini_alter($key, '1'); // error
    }

    public function harmless(): void
    {
        ini_alter('user_agent', 'demo');
    }
}
