<?php

declare(strict_types=1);

namespace DemoWorker\Form;

final class OrderOptions
{
    private int $n = 0;

    public function bump(): void
    {
        ++$this->n; // skipped by /form/ namespace fragment
    }
}
