<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import([
        "path" => "../src/Shared/Infrastructure/Symfony/Controller/Health/",
        "namespace" => 'App\Shared\Infrastructure\Symfony\Controller\Health',
    ], "attribute");

    $routingConfigurator->import([
        "path" => "../src/Shared/Infrastructure/Symfony/Controller/Api/",
        "namespace" => 'App\Shared\Infrastructure\Symfony\Controller\Api',
    ], "attribute")
        ->prefix("/api");
};
