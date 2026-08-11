<?php

declare(strict_types=1);

namespace DemoHardening;

final class NoPcntlSignal
{
    public function bad(): void
    {
        pcntl_signal(SIGTERM, static function (): void {}); // error
        pcntl_async_signals(true); // error
        pcntl_signal_dispatch(); // error
        pcntl_signal_get_handler(SIGTERM); // error
        pcntl_sigprocmask(SIG_BLOCK, [SIGHUP]); // error
        pcntl_sigwaitinfo([SIGTERM]); // error
        pcntl_sigtimedwait([SIGTERM], $info, 0); // error
        pcntl_alarm(1); // error
    }
}
