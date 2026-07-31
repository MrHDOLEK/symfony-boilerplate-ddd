<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Infrastructure\Doctrine\Type;

use App\Shared\Domain\Id;
use App\Shared\Domain\InvalidId;
use App\Shared\Infrastructure\Doctrine\Type\IdType;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class IdTypeTest extends TestCase
{
    private const string UUID = "550e8400-e29b-41d4-a716-446655440000";

    private IdType $type;
    private PostgreSQLPlatform $platform;

    protected function setUp(): void
    {
        $this->type = new IdType();
        $this->platform = new PostgreSQLPlatform();
    }

    public function testConvertsDatabaseStringToIdValueObject(): void
    {
        $id = $this->type->convertToPHPValue(self::UUID, $this->platform);

        $this->assertInstanceOf(Id::class, $id);
        $this->assertSame(self::UUID, $id->toString());
    }

    public function testConvertsIdValueObjectToDatabaseString(): void
    {
        $this->assertSame(
            self::UUID,
            $this->type->convertToDatabaseValue(new Id(self::UUID), $this->platform),
        );
    }

    public function testPassesNullThroughInBothDirections(): void
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    public function testRejectsAMalformedDatabaseValue(): void
    {
        $this->expectException(InvalidId::class);

        $this->type->convertToPHPValue("not-a-uuid", $this->platform);
    }

    public function testRejectsAValueItCannotMap(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->type->convertToDatabaseValue(42, $this->platform);
    }
}
