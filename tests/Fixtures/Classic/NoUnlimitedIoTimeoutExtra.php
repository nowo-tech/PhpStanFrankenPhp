<?php

declare(strict_types=1);

namespace DemoClassic;

use Symfony\Component\Process\Process;

final class NoUnlimitedIoTimeoutExtra
{
    public function idle(Process $process): void
    {
        $process->setIdleTimeout(0);
    }

    public function curlArray($ch): void
    {
        curl_setopt_array($ch, [
            \CURLOPT_TIMEOUT_MS => 0,
        ]);
    }
}
