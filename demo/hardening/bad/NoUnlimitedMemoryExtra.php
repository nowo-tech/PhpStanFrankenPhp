<?php

declare(strict_types=1);

namespace DemoHardening;

final class NoUnlimitedMemoryExtra
{
    public function viaInt(): void
    {
        ini_set('memory_limit', -1);
    }

    public function viaUnary(): void
    {
        ini_set('memory_limit', -1);
    }

    public function finite(): void
    {
        ini_set('memory_limit', '256M');
    }
}
