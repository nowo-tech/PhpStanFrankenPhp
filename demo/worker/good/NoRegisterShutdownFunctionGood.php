<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoRegisterShutdownFunctionGood
{
    public function afterResponse(JobBus $bus): void
    {
        $bus->dispatch(new SendEmailJob());
    }
}
