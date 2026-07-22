<?php

declare(strict_types=1);

namespace DemoClassic\Good;

use Symfony\Component\HttpFoundation\Response;

final class NoExitOrDieGood
{
    public function handle(): Response
    {
        return new Response('ok', Response::HTTP_OK);
    }
}
