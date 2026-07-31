<?php

declare(strict_types=1);

use App\Shared\Infrastructure\Symfony\Listener\ExceptionListener;
use App\Shared\Infrastructure\Symfony\Request\Resolver\JsonBodyResolver;
use App\Shared\Infrastructure\Symfony\Request\Resolver\QueryEnumResolver;
use App\Shared\Infrastructure\Symfony\Request\Resolver\QueryRequestResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load("App\\", __DIR__ . "/../src/")
        ->exclude([
            __DIR__ . "/../src/Kernel.php",
            __DIR__ . "/../src/Shared/Domain/",
            __DIR__ . "/../src/Shared/Infrastructure/Doctrine/Type/",
        ]);

    $services->set(ExceptionListener::class)
        ->arg("\$environment", "%kernel.environment%")
        ->tag("kernel.event_listener", [
            "event" => "kernel.exception",
        ]);

    $services->set(QueryRequestResolver::class)
        ->tag("controller.argument_value_resolver", [
            "priority" => -40,
        ]);

    $services->set(QueryEnumResolver::class)
        ->tag("controller.argument_value_resolver", [
            "priority" => -45,
        ]);

    $services->set(JsonBodyResolver::class)
        ->tag("controller.argument_value_resolver", [
            "priority" => -50,
        ]);
};
