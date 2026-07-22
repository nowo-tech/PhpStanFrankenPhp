<?php

declare(strict_types=1);

namespace DemoWorker;

final class Registry
{
    public static function getInstance(): self
    {
        return new self();
    }
}

final class NoSingletonGetInstance
{
    public function bad(): Registry
    {
        return Registry::getInstance(); // error
    }
}
