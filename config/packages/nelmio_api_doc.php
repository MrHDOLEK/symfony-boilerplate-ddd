<?php

declare(strict_types=1);

use App\Shared\Infrastructure\Symfony\Controller\IndexController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension("nelmio_api_doc", [
        "documentation" => [
            "info" => [
                "title" => "Symfony API",
                "description" => "This is an api!",
                "version" => "1.0.0",
            ],
        ],
        "models" => [
            "names" => [
                [
                    "alias" => "IndexController",
                    "type" => IndexController::class,
                ],
            ],
        ],
        "areas" => [
            "path_patterns" => [
                "^/api/",
            ],
        ],
    ]);
};
