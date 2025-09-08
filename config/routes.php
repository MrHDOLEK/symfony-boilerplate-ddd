<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import([
        "path" => "../src/Shared/Infrastructure/Symfony/Controller/",
        "namespace" => 'App\Shared\Infrastructure\Symfony\Controller',
    ], "attribute")
        ->prefix("/api");
};
