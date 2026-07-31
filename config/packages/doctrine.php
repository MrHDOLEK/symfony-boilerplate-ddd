<?php

declare(strict_types=1);

use App\Shared\Infrastructure\Doctrine\Type\IdType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension("doctrine", [
        "dbal" => [
            "driver" => "pdo_pgsql",
            "host" => "%env(DB_HOST)%",
            "port" => "%env(int:DB_PORT)%",
            "dbname" => "%env(DB_NAME)%",
            "user" => "%env(DB_USER)%",
            "password" => "%env(DB_PASSWORD)%",
            "server_version" => "17",
            "charset" => "utf8",
            "profiling_collect_backtrace" => "%kernel.debug%",
            "types" => [
                IdType::NAME => IdType::class,
            ],
        ],
        "orm" => [
            "enable_native_lazy_objects" => true,
            "validate_xml_mapping" => true,
            "naming_strategy" => "doctrine.orm.naming_strategy.underscore_number_aware",
            "auto_mapping" => true,
        ],
    ]);

    if ($containerConfigurator->env() === "prod") {
        $containerConfigurator->extension("doctrine", [
            "orm" => [
                "query_cache_driver" => [
                    "type" => "pool",
                    "pool" => "doctrine.system_cache_pool",
                ],
                "result_cache_driver" => [
                    "type" => "pool",
                    "pool" => "doctrine.result_cache_pool",
                ],
                "metadata_cache_driver" => [
                    "type" => "pool",
                    "pool" => "doctrine.system_cache_pool",
                ],
            ],
        ]);
        $containerConfigurator->extension("framework", [
            "cache" => [
                "pools" => [
                    "doctrine.result_cache_pool" => [
                        "adapter" => "cache.app",
                    ],
                    "doctrine.system_cache_pool" => [
                        "adapter" => "cache.system",
                    ],
                ],
            ],
        ]);
    }
};
