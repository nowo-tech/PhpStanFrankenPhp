<?php

declare(strict_types=1);

namespace DemoWorker\Good;

use Symfony\Contracts\Service\ResetInterface;

/**
 * Compliant worker-level patterns: instance state + ResetInterface.
 */
final class RequestScopedService implements ResetInterface
{
    private ?string $userId = null;

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function reset(): void
    {
        $this->userId = null;
    }
}
