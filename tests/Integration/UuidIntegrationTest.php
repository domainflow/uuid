<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Integration;

use DomainFlow\Uuid\Inspector;
use DomainFlow\Uuid\UuidV1;
use DomainFlow\Uuid\UuidV2;
use DomainFlow\Uuid\UuidV3;
use DomainFlow\Uuid\UuidV4;
use DomainFlow\Uuid\UuidV5;
use DomainFlow\Uuid\UuidV6;
use DomainFlow\Uuid\UuidV7;
use DomainFlow\Uuid\UuidV8;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

#[CoversNothing()]
final class UuidIntegrationTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function testUuidV1GenerationAndValidation(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $uuid = UuidV1::generate();
            $inspector = Inspector::analyze((string) $uuid);
            $this->assertSame(1, $inspector->version());
            $this->assertSame('RFC 4122', $inspector->variant());
        }
    }

    /**
     * @throws RandomException
     */
    public function testUuidV2GenerationAndValidation(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $uuid = UuidV2::generate(1000, 'uid');
            $inspector = Inspector::analyze((string) $uuid);
            $this->assertSame(2, $inspector->version());
            $this->assertSame('RFC 4122', $inspector->variant());
        }
    }

    public function testUuidV3GenerationAndValidation(): void
    {
        $namespace = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
        for ($i = 0; $i < 100; $i++) {
            $uuid = UuidV3::generate($namespace, 'test');
            $inspector = Inspector::analyze((string) $uuid);
            $this->assertSame(3, $inspector->version());
            $this->assertSame('RFC 4122', $inspector->variant());
        }
    }

    /**
     * @throws RandomException
     */
    public function testUuidV4GenerationAndValidation(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $uuid = UuidV4::generate();
            $inspector = Inspector::analyze((string) $uuid);
            $this->assertSame(4, $inspector->version());
            $this->assertSame('RFC 4122', $inspector->variant());
        }
    }

    public function testUuidV5GenerationAndValidation(): void
    {
        $namespace = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
        for ($i = 0; $i < 100; $i++) {
            $uuid = UuidV5::generate($namespace, 'test');
            $inspector = Inspector::analyze((string) $uuid);
            $this->assertSame(5, $inspector->version());
            $this->assertSame('RFC 4122', $inspector->variant());
        }
    }

    /**
     * @throws RandomException
     */
    public function testUuidV6GenerationAndValidation(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $uuid = UuidV6::generate();
            $inspector = Inspector::analyze((string) $uuid);
            $this->assertSame(6, $inspector->version());
            $this->assertSame('RFC 4122', $inspector->variant());
        }
    }

    /**
     * @throws RandomException
     */
    public function testUuidV7GenerationAndValidation(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $uuid = UuidV7::generate();
            $inspector = Inspector::analyze((string) $uuid);
            $this->assertSame(7, $inspector->version());
            $this->assertSame('RFC 4122', $inspector->variant());
        }
    }

    /**
     * @throws RandomException
     */
    public function testUuidV8GenerationAndValidation(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $uuid = UuidV8::generate();
            $inspector = Inspector::analyze((string) $uuid);
            $this->assertSame(8, $inspector->version());
            $this->assertSame('RFC 4122', $inspector->variant());
        }
    }
}
