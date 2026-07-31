<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Controller\Health;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final class ReadinessAction
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route("/readyz", name: "health.ready", methods: ["GET"])]
    public function __invoke(): JsonResponse
    {
        try {
            $this->connection->executeQuery($this->connection->getDatabasePlatform()->getDummySelectSQL());
        } catch (Throwable $exception) {
            $this->logger->error("Readiness probe failed.", [
                "dependency" => "database",
                "reason" => $exception->getMessage(),
            ]);

            return new JsonResponse([
                "status" => "unavailable",
                "dependency" => "database",
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new JsonResponse([
            "status" => "ok",
        ]);
    }
}
