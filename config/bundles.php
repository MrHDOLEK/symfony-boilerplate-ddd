<?php

declare(strict_types=1);

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;

return [
    FrameworkBundle::class => ["all" => true],
    DebugBundle::class => ["dev" => true],
    TwigBundle::class => ["all" => true],
    WebProfilerBundle::class => ["dev" => true, "test" => true],
    DoctrineBundle::class => ["all" => true],
    DoctrineMigrationsBundle::class => ["all" => true],
    FriendsOfBehatSymfonyExtensionBundle::class => ["test" => true],
    DAMADoctrineTestBundle::class => ["test" => true],
    NelmioApiDocBundle::class => ["all" => true],
];
