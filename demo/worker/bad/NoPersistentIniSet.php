<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoPersistentIniSet
{
    public function bad(): void
    {
        ini_set('memory_limit', '256M'); // error
    }
}
