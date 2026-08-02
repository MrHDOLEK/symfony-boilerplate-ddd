<?php

declare(strict_types=1);

use Blumilk\Codestyle\Config;
use Blumilk\Codestyle\Configuration\Defaults\Paths;

$config = new Config(
    paths: new Paths(
        "src",
        "tests",
        "public",
        "migrations",
        "config/packages",
        "config/routes",
        "config/bundles.php",
        "config/preload.php",
        "config/routes.php",
        "config/services.php",
        "config/services_test.php",
        "codestyle.php",
    ),
);

return $config->config();
