<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoUmaskGood
{
    public function currentMask(): int
    {
        // Set umask in the supervisor / container entrypoint; reading is fine.
        return umask();
    }
}
