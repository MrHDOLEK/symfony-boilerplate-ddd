<?php

declare(strict_types=1);

use App\Kernel;
use App\Tests\Integration\Context\ApiContext;
use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;
use DAMA\DoctrineTestBundle\Behat\ServiceContainer\DoctrineExtension;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;

return (new Config())
    ->withProfile(
        (new Profile("default", [
            "calls" => [
                "error_reporting" => 16383,
            ],
        ]))
            ->withSuite(
                (new Suite("default"))
                    ->withPaths("%paths.base%/tests/integration/Feature/default")
                    ->withContexts(ApiContext::class),
            )
            ->withExtension(
                new Extension(SymfonyExtension::class, [
                    "kernel" => [
                        "environment" => "test",
                        "class" => Kernel::class,
                    ],
                    "bootstrap" => "tests/bootstrap.php",
                ]),
            )
            ->withExtension(
                new Extension(DoctrineExtension::class, []),
            ),
    );
