<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoSuperglobalAccess
{
    public function bad(): mixed
    {
        $_ENV['TOKEN'] = 'x'; // error

        return $_SESSION['user']; // error
    }

    public function allowedByDefault(): mixed
    {
        // FrankenPHP resets $_GET between requests — not flagged unless strict mode.
        return $_GET['id'] ?? null;
    }
}
