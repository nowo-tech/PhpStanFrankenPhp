<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoPersistentIniSetExtra
{
    public function dynamicKey(string $key): void
    {
        ini_set($key, '1');
    }

    public function harmless(): void
    {
        ini_set('user_agent', 'demo');
    }
}
