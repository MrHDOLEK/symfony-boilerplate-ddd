<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension("doctrine", [
        "dbal" => [
            "driver" => "pdo_pgsql",
            "host" => "%env(DB_HOST)%",
            "port" => "%env(DB_PORT)%",
            "dbname" => "%env(DB_NAME)%",
            "user" => "%env(DB_USER)%",
            "password" => "%env(DB_PASSWORD)%",
            "server_version" => "16.0",
            "charset" => "utf8",
            "profiling_collect_backtrace" => "%kernel.debug%",
            "default_table_options" => [
                "charset" => "utf8",
                "collation" => "utf8_general_ci",
            ],
        ],
        "orm" => [
            "auto_generate_proxy_classes" => true,
            "enable_lazy_ghost_objects" => true,
            "report_fields_where_declared" => true,
            "validate_xml_mapping" => true,
            "naming_strategy" => "doctrine.orm.naming_strategy.underscore_number_aware",
            "auto_mapping" => true,
        ],
    ]);

    if ($containerConfigurator->env() === "test") {
        $containerConfigurator->extension("doctrine", []);
    }

    if ($containerConfigurator->env() === "prod") {
        $containerConfigurator->extension("doctrine", [
            "orm" => [
                "auto_generate_proxy_classes" => false,
                "proxy_dir" => "%kernel.build_dir%/doctrine/orm/Proxies",
                "query_cache_driver" => [
                    "type" => "pool",
                    "pool" => "doctrine.system_cache_pool",
                ],
                "result_cache_driver" => [
                    "type" => "pool",
                    "pool" => "doctrine.result_cache_pool",
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
