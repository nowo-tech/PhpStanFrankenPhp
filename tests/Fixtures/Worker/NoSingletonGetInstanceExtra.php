<?php

declare(strict_types=1);

namespace DemoWorker;

final class Holder
{
    public function getInstance(): self
    {
        return $this;
    }
}

final class NoSingletonGetInstanceExtra
{
    public function viaObject(Holder $holder): Holder
    {
        return $holder->getInstance();
    }
}
