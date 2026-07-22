<?php

declare(strict_types=1);

namespace DemoClassic\Good;

use Symfony\Component\HttpFoundation\Response;

final class NoFastCgiFinishRequestGood
{
    public function handle(): Response
    {
        // Return the response; enqueue post-response work instead of fastcgi_finish_request().
        return new Response('accepted', Response::HTTP_ACCEPTED);
    }
}
