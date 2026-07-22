<?php

declare(strict_types=1);

namespace App\Good;

use Symfony\Contracts\Service\ResetInterface;

/**
 * Worker-safe request counter (cleared between requests via kernel.reset).
 */
final class RequestScopedCounter implements ResetInterface
{
    private int $hits = 0;

    public function hit(): void
    {
        ++$this->hits;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function reset(): void
    {
        $this->hits = 0;
    }
}
