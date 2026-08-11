<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoMbEncodingMutationGood
{
    public function toUpper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
