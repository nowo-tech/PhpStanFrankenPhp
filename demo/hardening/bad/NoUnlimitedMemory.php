<?php

declare(strict_types=1);

namespace DemoHardening;

final class NoUnlimitedMemory
{
    public function bad(): void
    {
        ini_set('memory_limit', '-1'); // error
    }
}
