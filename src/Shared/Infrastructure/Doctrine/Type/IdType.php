<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Type;

use App\Shared\Domain\Id;

final class IdType extends AbstractIdType
{
    public const string NAME = "id";

    protected function fromString(string $value): Id
    {
        return Id::fromString($value);
    }
}
