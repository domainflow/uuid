<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Unit\Uuid\Trait;

use DomainFlow\Uuid\Interface\UuidInterface;
use DomainFlow\Uuid\UuidV1;
use DomainFlow\Uuid\UuidV2;
use DomainFlow\Uuid\UuidV3;
use DomainFlow\Uuid\UuidV4;
use DomainFlow\Uuid\UuidV5;
use DomainFlow\Uuid\UuidV6;
use DomainFlow\Uuid\UuidV7;
use DomainFlow\Uuid\UuidV8;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

#[CoversClass(UuidV1::class)]
#[CoversClass(UuidV2::class)]
#[CoversClass(UuidV3::class)]
#[CoversClass(UuidV4::class)]
#[CoversClass(UuidV5::class)]
#[CoversClass(UuidV6::class)]
#[CoversClass(UuidV7::class)]
#[CoversClass(UuidV8::class)]
final class UuidMethodsTraitTest extends TestCase
{
    /**
     * @throws RandomException
     * @return array<int, array<int, class-string|UuidInterface|int|string>>
     */
    public static function uuidClasses(): array
    {
        return [
            [UuidV1::class],
            [UuidV2::class, 123, 'uid'],
            [UuidV3::class, UuidV3::generate(UuidV4::generate()->__toString(), 'test-ns'), 'test'],
            [UuidV4::class],
            [UuidV5::class, UuidV5::generate(UuidV4::generate()->__toString(), 'test-ns'), 'test'],
            [UuidV6::class],
            [UuidV7::class],
            [UuidV8::class],
        ];
    }

    public static function uuidClassNames(): array
    {
        return [
            [UuidV1::class],
            [UuidV2::class],
            [UuidV3::class],
            [UuidV4::class],
            [UuidV5::class],
            [UuidV6::class],
            [UuidV7::class],
            [UuidV8::class],
        ];
    }

    /**
     * @param string $class
     * @param mixed ...$args
     * @return void
     */
    #[DataProvider('uuidClasses')]
    public function test_createsValidInstanceFromString(string $class, ...$args): void
    {
        $uuid = match ($class) {
            UuidV2::class => $class::generate($args[0], $args[1]),
            UuidV3::class, UuidV5::class => $args[0],
            default => $class::generate(),
        };

        $instance = $class::fromString((string) $uuid);

        $this->assertInstanceOf($class, $instance);
        $this->assertSame((string) $uuid, (string) $instance);
    }

    /**
     * @param string $class
     * @param mixed ...$args
     * @throws RandomException
     * @return void
     */
    #[DataProvider('uuidClasses')]
    public function test_fromJsonValid(string $class, ...$args): void
    {
        if (in_array($class, [UuidV3::class, UuidV5::class], true)) {
            /** @var UuidInterface $uuid */
            $uuid = $args[0]; // pre-generated UUID instance
        } elseif ($class === UuidV2::class) {
            $uuid = $class::generate($args[0], $args[1]);
        } else {
            $uuid = $class::generate();
        }

        $json = json_encode((string) $uuid);
        $fromJson = $class::fromJson($json);

        $this->assertInstanceOf($class, $fromJson);
        $this->assertTrue($uuid->equals($fromJson));
    }

    #[DataProvider('uuidClasses')]
    public function test_fromJsonInvalidJson(string $class): void
    {
        $this->expectException(JsonException::class);
        $class::fromJson('{invalid-json}');
    }

    #[DataProvider('uuidClasses')]
    public function test_fromJsonValidButNotString(string $class): void
    {
        $this->expectException(JsonException::class);
        $class::fromJson(json_encode(['uuid' => 'not-a-flat-string']));
    }

    #[DataProvider('uuidClasses')]
    public function test_fromJsonValidButNotAUuid(string $class): void
    {
        $this->expectException(InvalidArgumentException::class);
        $class::fromJson(json_encode("this-is-not-a-uuid"));
    }

    #[DataProvider('uuidClassNames')]
    public function test_fromStringThrowsExceptionForInvalidUuid(string $class): void
    {
        $this->expectException(InvalidArgumentException::class);
        $class::fromString('not-a-valid-uuid');
    }

    #[DataProvider('uuidClasses')]
    public function test_fromStringCreatesSameUuid(string $class, ...$args): void
    {
        $uuid = match ($class) {
            UuidV2::class => $class::generate($args[0], $args[1]),
            UuidV3::class, UuidV5::class => $args[0],
            default => $class::generate(),
        };

        $fromString = $class::fromString((string) $uuid);
        $this->assertTrue($uuid->equals($fromString));
    }

    #[DataProvider('uuidClasses')]
    public function test_toStringReturnsUuidString(string $class, ...$args): void
    {
        $uuid = match ($class) {
            UuidV2::class => $class::generate($args[0], $args[1]),
            UuidV3::class, UuidV5::class => $args[0],
            default => $class::generate(),
        };

        $this->assertIsString((string) $uuid);
        $this->assertMatchesRegularExpression('/^[0-9a-f\-]{36}$/i', (string) $uuid);
    }

    #[DataProvider('uuidClasses')]
    public function test_jsonSerializeReturnsUuidString(string $class, ...$args): void
    {
        $uuid = match ($class) {
            UuidV2::class => $class::generate($args[0], $args[1]),
            UuidV3::class, UuidV5::class => $args[0],
            default => $class::generate(),
        };

        $this->assertSame((string) $uuid, $uuid->jsonSerialize());
        $this->assertJsonStringEqualsJsonString(
            json_encode((string) $uuid),
            json_encode($uuid)
        );
    }

    #[DataProvider('uuidClasses')]
    public function test_equals(string $class, ...$args): void
    {
        $uuid = match ($class) {
            UuidV2::class => $class::generate($args[0], $args[1]),
            UuidV3::class, UuidV5::class => $args[0],
            default => $class::generate(),
        };

        $copy = $class::fromString((string) $uuid);
        $this->assertTrue($uuid->equals($copy));

        $different = match ($class) {
            UuidV2::class => $class::generate($args[0] + 1, $args[1]),
            UuidV3::class, UuidV5::class => $class::generate(UuidV4::generate()->__toString(), 'different'),
            default => $class::generate(),
        };

        $this->assertFalse($uuid->equals($different));
    }
}
