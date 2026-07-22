<?php

declare(strict_types=1);

namespace DemoClassic\Good;

use Symfony\Component\Process\Process;

final class NoUnlimitedIoTimeoutGood
{
    public function run(): string
    {
        $process = new Process(['echo', 'ok']);
        $process->setTimeout(10);
        $process->setIdleTimeout(5);
        $process->mustRun();

        return $process->getOutput();
    }

    public function curlWithTimeout($ch): void
    {
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    }
}
