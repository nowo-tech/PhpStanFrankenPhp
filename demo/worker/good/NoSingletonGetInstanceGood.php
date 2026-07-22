<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoSingletonGetInstanceGood
{
    public function __construct(
        private readonly Registry $registry,
    ) {
    }

    public function registry(): Registry
    {
        return $this->registry;
    }
}
