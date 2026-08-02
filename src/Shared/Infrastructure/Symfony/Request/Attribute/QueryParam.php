<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Request\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class QueryParam
{
    public function __construct(
        public string $name,
    ) {}
}
