<?php

declare(strict_types=1);

namespace DemoClassic;

use Symfony\Component\Process\Process;

final class NoUnlimitedIoTimeout
{
    public function badProcess(Process $process): void
    {
        $process->setTimeout(null); // error
    }

    public function badCurl($ch): void
    {
        curl_setopt($ch, \CURLOPT_TIMEOUT, 0); // error
    }

    public function badProc(): void
    {
        proc_open('true', [], $pipes); // error
    }
}
