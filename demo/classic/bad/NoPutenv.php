<?php

declare(strict_types=1);

namespace DemoClassic;

final class NoPutenv
{
    public function bad(): void
    {
        putenv('FOO=bar'); // error
    }
}
