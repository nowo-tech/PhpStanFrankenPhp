<?php

declare(strict_types=1);

namespace DemoHardening;

final class NoRegisterTickFunction
{
    public function bad(): void
    {
        register_tick_function(static function (): void {}); // error
    }
}
