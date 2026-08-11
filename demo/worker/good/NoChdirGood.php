<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoChdirGood
{
    public function resolve(string $basePath, string $relative): string
    {
        return rtrim($basePath, '/').'/'.ltrim($relative, '/');
    }
}
