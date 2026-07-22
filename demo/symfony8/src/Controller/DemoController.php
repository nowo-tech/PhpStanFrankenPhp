<?php

declare(strict_types=1);

namespace App\Controller;

use App\Good\RequestScopedCounter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DemoController extends AbstractController
{
    public function __construct(
        private readonly RequestScopedCounter $counter,
        #[Autowire('%env(FRANKENPHP_MODE)%')]
        private readonly string $frankenphpMode,
    ) {
    }

    #[Route('/', name: 'demo_home', methods: ['GET'])]
    public function home(): Response
    {
        $this->counter->hit();

        return $this->render('demo/home.html.twig', [
            'hits' => $this->counter->getHits(),
            'frankenphpMode' => $this->frankenphpMode,
        ]);
    }
}
