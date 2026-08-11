<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoMbEncodingMutation
{
    public function bad(): void
    {
        mb_internal_encoding('UTF-8'); // error
        mb_regex_encoding('UTF-8'); // error
        mb_http_output('UTF-8'); // error
        mb_language('uni'); // error
    }

    public function readsAreAllowed(): void
    {
        mb_internal_encoding();
        mb_regex_encoding();
        mb_http_output();
        mb_language();
    }
}
