<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Unit\Uuid;

use DomainFlow\Uuid\UuidV3;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UuidV3::class)]
final class UuidV3Test extends TestCase
{
    private string $namespace;
    private string $name;

    protected function setUp(): void
    {
        $this->namespace = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
        $this->name = 'example.com';
    }

    public function test_generateReturnsDeterministicUuidV3(): void
    {
        $uuid1 = UuidV3::generate($this->namespace, $this->name);
        $uuid2 = UuidV3::generate($this->namespace, $this->name);

        $this->assertSame((string) $uuid1, (string) $uuid2);
        $this->assertTrue(UuidV3::isValid((string) $uuid1));
    }

    public function test_fromNamespaceAndNameMatchesGenerate(): void
    {
        $uuid1 = UuidV3::generate($this->namespace, $this->name);
        $uuid2 = UuidV3::fromNamespaceAndName($this->namespace, $this->name);

        $this->assertSame((string) $uuid1, (string) $uuid2);
    }

    public function test_isValidAcceptsValidUuidV3(): void
    {
        $uuid = UuidV3::generate($this->namespace, $this->name);
        $this->assertTrue(UuidV3::isValid((string) $uuid));
    }

    public function test_isValidRejectsInvalidUuid(): void
    {
        $this->assertFalse(UuidV3::isValid('not-a-valid-uuid'));
    }

    public function test_fromNamespaceAndNameThrowsOnInvalidNamespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UuidV3::fromNamespaceAndName('invalid-uuid', $this->name);
    }

}
