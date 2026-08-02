<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use Stringable;

readonly class Id implements Stringable
{
    private const string ID_FORMAT = "/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i";

    final public function __construct(
        private string $id,
    ) {
        $this->guard();
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public static function fromString(string $id): static
    {
        return new static($id);
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function equals(self $otherId): bool
    {
        return static::class === $otherId::class && $this->id === $otherId->id;
    }

    private function guard(): void
    {
        if (!preg_match(self::ID_FORMAT, $this->id)) {
            throw new InvalidId();
        }
    }
}
