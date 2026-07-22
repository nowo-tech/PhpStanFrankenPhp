<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoStaticLocalVariableGood
{
    public function next(int $current): int
    {
        return $current + 1;
    }
}
