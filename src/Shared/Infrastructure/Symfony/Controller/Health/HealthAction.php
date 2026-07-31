<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Controller\Health;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthAction
{
    #[Route("/healthz", name: "health.live", methods: ["GET"])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            "status" => "ok",
        ]);
    }
}
