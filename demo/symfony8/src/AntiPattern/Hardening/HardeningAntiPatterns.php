<?php

declare(strict_types=1);

namespace App\AntiPattern\Hardening;

/**
 * Intentional hardening-level violations for phpstan-frankenphp demos.
 * Do not copy into production code.
 */
final class HardeningAntiPatterns
{
    public function unlimitedTime(): void
    {
        set_time_limit(0);
    }

    public function unlimitedMemory(): void
    {
        ini_set('memory_limit', '-1');
    }

    public function fork(): void
    {
        if (\function_exists('pcntl_fork')) {
            pcntl_fork();
        }
    }

    public function sleepInRequest(): void
    {
        sleep(1);
    }

    public function ticks(): void
    {
        register_tick_function(static function (): void {});
    }

    public function signals(): void
    {
        if (\function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, static function (): void {});
            pcntl_alarm(1);
        }
    }
}
