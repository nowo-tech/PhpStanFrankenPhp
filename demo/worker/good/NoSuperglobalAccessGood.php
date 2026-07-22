<?php

declare(strict_types=1);

namespace DemoWorker\Good;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class NoSuperglobalAccessGood
{
    public function id(Request $request): ?string
    {
        $id = $request->query->get('id');

        return is_string($id) ? $id : null;
    }

    public function userId(SessionInterface $session): mixed
    {
        return $session->get('user_id');
    }
}
