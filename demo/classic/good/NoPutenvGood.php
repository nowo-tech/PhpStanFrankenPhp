<?php

declare(strict_types=1);

namespace DemoClassic\Good;

final class NoPutenvGood
{
    public function __construct(
        private readonly string $apiKey,
    ) {
    }

    public function key(): string
    {
        return $this->apiKey;
    }
}
