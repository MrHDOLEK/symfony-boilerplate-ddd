<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Type;

use App\Shared\Domain\Id;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use InvalidArgumentException;

abstract class AbstractIdType extends GuidType
{
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Id
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Id) {
            return $value;
        }

        if (is_string($value)) {
            return $this->fromString($value);
        }

        throw new InvalidArgumentException(
            sprintf("Cannot convert a %s database value to %s.", get_debug_type($value), static::class),
        );
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Id) {
            return $value->toString();
        }

        if (is_string($value)) {
            return $value;
        }

        throw new InvalidArgumentException(
            sprintf("Cannot convert a %s value to a %s database value.", get_debug_type($value), static::class),
        );
    }

    abstract protected function fromString(string $value): Id;
}
