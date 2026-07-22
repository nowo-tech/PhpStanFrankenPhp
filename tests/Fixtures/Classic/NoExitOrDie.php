<?php

declare(strict_types=1);

namespace DemoClassic;

final class NoExitOrDie
{
    public function bad(): void
    {
        exit(1); // error
    }

    public function alsoBad(): void
    {
        exit('no'); // error
    }

    public function good(): int
    {
        return 0;
    }
}
