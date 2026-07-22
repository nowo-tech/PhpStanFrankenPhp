<?php

declare(strict_types=1);

namespace App\AntiPattern\Classic;

/**
 * Intentional classic-level violations for phpstan-frankenphp demos.
 * Do not copy into production code.
 */
final class ClassicAntiPatterns
{
    public function exitEarly(): never
    {
        exit(1);
    }

    public function finishFastCgi(): void
    {
        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    public function mutateEnv(): void
    {
        putenv('DEMO_TOKEN=secret');
    }

    public function ignoreAbort(): void
    {
        ignore_user_abort(true);
    }

    public function unlimitedProcess(\Symfony\Component\Process\Process $process): void
    {
        $process->setTimeout(null);
    }
}
