<?php

declare(strict_types=1);

namespace DemoWorker\Good;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class NoNativeSessionApiGood
{
    public function remember(SessionInterface $session, string $key, mixed $value): void
    {
        $session->set($key, $value);
    }
}
