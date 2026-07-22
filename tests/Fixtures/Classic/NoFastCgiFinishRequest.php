<?php

declare(strict_types=1);

namespace DemoClassic;

final class NoFastCgiFinishRequest
{
    public function bad(): void
    {
        fastcgi_finish_request(); // error
    }
}
