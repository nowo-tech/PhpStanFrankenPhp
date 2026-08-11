<?php

declare(strict_types=1);

namespace App\AntiPattern\Worker;

/**
 * Intentional worker-level violations for phpstan-frankenphp demos.
 * Do not copy into production code.
 */
final class WorkerAntiPatterns
{
    private static array $cache = [];

    public function staticCache(string $key, mixed $value): mixed
    {
        self::$cache[$key] = $value;

        return self::$cache[$key];
    }

    public function staticLocal(): int
    {
        static $count = 0;

        return ++$count;
    }

    public function globals(): void
    {
        global $demoShared;
        $GLOBALS['demo'] = 1;
        $demoShared = 1;
    }

    public function envAndSession(): mixed
    {
        $_ENV['TOKEN'] = 'x';

        return $_SESSION['user'] ?? null;
    }

    public function nativeSession(): void
    {
        session_start();
    }

    public function stickyIni(): void
    {
        ini_set('memory_limit', '256M');
    }

    public function singleton(): Registry
    {
        return Registry::getInstance();
    }

    public function shutdown(): void
    {
        register_shutdown_function(static function (): void {});
    }

    public function handlers(): void
    {
        set_error_handler(static fn (): bool => true);
        set_exception_handler(static function (\Throwable $e): void {});
    }

    public function chdirLeak(): void
    {
        chdir('/tmp');
    }

    public function localeLeak(): void
    {
        setlocale(LC_ALL, 'en_US.UTF-8');
    }

    public function intlLocaleLeak(): void
    {
        locale_set_default('en_US');
    }

    public function timezoneLeak(): void
    {
        date_default_timezone_set('Europe/Madrid');
    }

    public function mbEncodingLeak(): void
    {
        mb_internal_encoding('UTF-8');
        mb_language('uni');
    }

    public function errorReportingLeak(): void
    {
        error_reporting(E_ALL);
    }

    public function umaskLeak(): void
    {
        umask(0o022);
    }
}

final class Registry
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }
}
