<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Unit\Uuid;

use DomainFlow\Uuid\UuidV5;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

#[CoversClass(UuidV5::class)]
final class UuidV5Test extends TestCase
{
    private string $namespace;
    private string $name;
    private string $expectedUuid;

    protected function setUp(): void
    {
        // Standard DNS namespace UUID
        $this->namespace = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
        $this->name = 'example.com';

        // Precomputed UUIDv5 of "example.com" with the DNS namespace
        $this->expectedUuid = '2ed6657d-e927-568b-95e1-2665a8aea6a2';
    }

    public function test_generatesConsistentDeterministicUuidV5(): void
    {
        $uuid1 = UuidV5::fromNamespaceAndName($this->namespace, $this->name);
        $uuid2 = UuidV5::fromNamespaceAndName($this->namespace, $this->name);

        $this->assertSame((string) $uuid1, (string) $uuid2);
        $this->assertTrue(UuidV5::isValid((string) $uuid1));
    }

    public function test_isValidRecognizesValidUuidV5(): void
    {
        $this->assertTrue(UuidV5::isValid($this->expectedUuid));
    }

    public function test_isValidRejectsInvalidUuid(): void
    {
        $this->assertFalse(UuidV5::isValid('not-a-uuid'));
    }

    public function test_fromNamespaceAndNameThrowsExceptionForInvalidNamespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UuidV5::fromNamespaceAndName('not-a-valid-uuid', 'example.com');
    }

    public function test_generateIsAliasForFromNamespaceAndName(): void
    {
        $generated = UuidV5::generate($this->namespace, $this->name);
        $fromMethod = UuidV5::fromNamespaceAndName($this->namespace, $this->name);

        $this->assertSame((string) $generated, (string) $fromMethod);
    }

    /**
     * @throws ReflectionException
     */
    public function test_uuidToBytesThrowsOnInvalidHex(): void
    {
        $reflection = new ReflectionClass(UuidV5::class);
        $method = $reflection->getMethod('uuidToBytes');

        $invalidUuid = 'zzzzzzzz-zzzz-zzzz-zzzz-zzzzzzzzzzzz'; // invalid hex

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to convert UUID to bytes: $invalidUuid");

        $method->invoke(null, $invalidUuid);
    }

}
