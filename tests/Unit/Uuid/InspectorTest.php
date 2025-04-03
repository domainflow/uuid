<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Unit\Uuid;

use DomainFlow\Uuid\Inspector;
use DomainFlow\Uuid\UuidV1;
use DomainFlow\Uuid\UuidV2;
use DomainFlow\Uuid\UuidV3;
use DomainFlow\Uuid\UuidV4;
use DomainFlow\Uuid\UuidV5;
use DomainFlow\Uuid\UuidV6;
use DomainFlow\Uuid\UuidV7;
use DomainFlow\Uuid\UuidV8;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

#[CoversClass(Inspector::class)]
#[CoversClass(UuidV1::class)]
#[CoversClass(UuidV2::class)]
#[CoversClass(UuidV3::class)]
#[CoversClass(UuidV4::class)]
#[CoversClass(UuidV5::class)]
#[CoversClass(UuidV6::class)]
#[CoversClass(UuidV7::class)]
#[CoversClass(UuidV8::class)]
final class InspectorTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function test_detectsVersionAndVariantForEachUuid(): void
    {
        $uuids = [
            UuidV1::generate(),
            UuidV2::generate(1000, 'uid'),
            UuidV3::generate('6ba7b810-9dad-11d1-80b4-00c04fd430c8', 'name'),
            UuidV4::generate(),
            UuidV5::generate('6ba7b810-9dad-11d1-80b4-00c04fd430c8', 'name'),
            UuidV6::generate(),
            UuidV7::generate(),
            UuidV8::generate(),
        ];

        foreach ($uuids as $uuid) {
            $inspector = Inspector::analyze((string) $uuid);

            $this->assertSame((int) substr((string) $uuid, 14, 1), $inspector->version());
            $this->assertSame('RFC 4122', $inspector->variant());

        }
    }

    /**
     * @throws RandomException
     */
    public function test_metadataIncludesExpectedKeysForVersion1(): void
    {
        $uuid = UuidV1::generate();
        $metadata = Inspector::analyze((string) $uuid)->metadata();

        $this->assertArrayHasKey('timestamp_100ns', $metadata);
        $this->assertArrayHasKey('clock_sequence', $metadata);
        $this->assertArrayHasKey('node', $metadata);
    }

    /**
     * @throws RandomException
     */
    public function test_metadataIncludesExpectedKeysForVersion2(): void
    {
        $uuid = UuidV2::generate(42, 'uid');
        $metadata = Inspector::analyze((string) $uuid)->metadata();

        $this->assertArrayHasKey('domain', $metadata);
        $this->assertArrayHasKey('local_identifier', $metadata);
    }

    public function test_metadataIncludesExpectedKeysForVersion3and5(): void
    {
        $uuid3 = UuidV3::generate('6ba7b810-9dad-11d1-80b4-00c04fd430c8', 'alpha');
        $uuid5 = UuidV5::generate('6ba7b810-9dad-11d1-80b4-00c04fd430c8', 'alpha');

        foreach ([$uuid3, $uuid5] as $uuid) {
            $meta = Inspector::analyze((string) $uuid)->metadata();
            $this->assertArrayHasKey('hash_type', $meta);
            $this->assertArrayHasKey('deterministic', $meta);
        }
    }

    /**
     * @throws RandomException
     */
    public function test_metadataIncludesExpectedKeysForVersion4(): void
    {
        $uuid = UuidV4::generate();
        $metadata = Inspector::analyze((string) $uuid)->metadata();

        $this->assertArrayHasKey('entropy_source', $metadata);
        $this->assertArrayHasKey('note', $metadata);
    }

    /**
     * @throws RandomException
     */
    public function test_metadataIncludesExpectedKeysForVersion6(): void
    {
        $uuid = UuidV6::generate();
        $metadata = Inspector::analyze((string) $uuid)->metadata();

        $this->assertArrayHasKey('timestamp_100ns', $metadata);
        $this->assertArrayHasKey('sortable', $metadata);
    }

    /**
     * @throws RandomException
     */
    public function test_metadataIncludesExpectedKeysForVersion7(): void
    {
        $uuid = UuidV7::generate();
        $metadata = Inspector::analyze((string) $uuid)->metadata();

        $this->assertArrayHasKey('unix_timestamp_ms', $metadata);
        $this->assertArrayHasKey('sortable', $metadata);
    }

    /**
     * @throws RandomException
     */
    public function test_metadataIncludesFallbackMessageForVersion8(): void
    {
        $uuid = UuidV8::generate();
        $metadata = Inspector::analyze((string) $uuid)->metadata();

        $this->assertArrayHasKey('info', $metadata);
        $this->assertSame('UUIDv8 is application-defined, structure unknown', $metadata['info']);
    }

    public function test_analyzeThrowsOnInvalidUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Inspector::analyze('not-a-valid-uuid');
    }

    /**
     * @throws RandomException
     */
    public function test_analyzeHandlesUnknownVersion(): void
    {
        $uuid = (string) UuidV4::generate();
        $uuidWithUnknownVersion = substr($uuid, 0, 14) . '9' . substr($uuid, 15);

        $inspector = Inspector::analyze($uuidWithUnknownVersion);
        $this->assertSame(9, $inspector->version());
        $this->assertArrayHasKey('error', $inspector->metadata());
    }

    /**
     * @throws RandomException
     */
    public function test_analyzeDetectsMicrosoftReservedVariant(): void
    {
        $uuid = (string) UuidV4::generate();
        $hex = str_replace('-', '', $uuid);

        // Force the variant byte to "c0" (0xC0 has bits 1100 0000, so ($variantByte & 0xE0) === 0xC0)
        $modifiedHex = substr($hex, 0, 16) . 'c0' . substr($hex, 18);
        $modifiedUuid = substr($modifiedHex, 0, 8) . '-'
            . substr($modifiedHex, 8, 4) . '-'
            . substr($modifiedHex, 12, 4) . '-'
            . substr($modifiedHex, 16, 4) . '-'
            . substr($modifiedHex, 20);

        $inspector = Inspector::analyze($modifiedUuid);
        $this->assertSame('Microsoft Reserved', $inspector->variant());
    }

    /**
     * @throws RandomException
     */
    public function test_analyzeDetectsNcsCompatibilityVariant(): void
    {
        $uuid = (string) UuidV4::generate();
        $hex = str_replace('-', '', $uuid);

        // Force the variant byte to "70" (0x70 is less than 0x80 so NCS Compatibility)
        $modifiedHex = substr($hex, 0, 16) . '70' . substr($hex, 18);
        $modifiedUuid = substr($modifiedHex, 0, 8) . '-'
            . substr($modifiedHex, 8, 4) . '-'
            . substr($modifiedHex, 12, 4) . '-'
            . substr($modifiedHex, 16, 4) . '-'
            . substr($modifiedHex, 20);

        $inspector = Inspector::analyze($modifiedUuid);
        $this->assertSame('NCS Compatibility', $inspector->variant());
    }

    /**
     * @throws RandomException
     */
    public function test_analyzeDetectsUnknownVariant(): void
    {
        $uuid = (string) UuidV4::generate();
        $hex = str_replace('-', '', $uuid);

        // Force the variant byte to "e0":
        // (0xe0 & 0xC0) is 0xC0, but (0xe0 & 0xE0) is 0xe0 which is not equal to 0xC0,
        // and (0xe0 & 0x80) is 0x80 (non-zero), so it falls to default.
        $modifiedHex = substr($hex, 0, 16) . 'e0' . substr($hex, 18);
        $modifiedUuid = substr($modifiedHex, 0, 8) . '-'
            . substr($modifiedHex, 8, 4) . '-'
            . substr($modifiedHex, 12, 4) . '-'
            . substr($modifiedHex, 16, 4) . '-'
            . substr($modifiedHex, 20);

        $inspector = Inspector::analyze($modifiedUuid);
        $this->assertSame('Unknown', $inspector->variant());
    }

    /**
     * @throws RandomException
     */
    public function test_parseV2DetectsPosixUid(): void
    {
        // Generate a valid UuidV2
        $uuid = (string) UuidV2::generate(1234, 'uid');
        $hex = str_replace('-', '', $uuid);

        // Force domain byte to "00" for POSIX UID
        $modifiedHex = substr_replace($hex, '00', 18, 2);
        $modifiedUuid = substr($modifiedHex, 0, 8) . '-'
            . substr($modifiedHex, 8, 4) . '-'
            . substr($modifiedHex, 12, 4) . '-'
            . substr($modifiedHex, 16, 4) . '-'
            . substr($modifiedHex, 20);

        $metadata = Inspector::analyze($modifiedUuid)->metadata();
        $this->assertSame('POSIX UID', $metadata['domain']);
    }

    /**
     * @throws RandomException
     */
    public function test_parseV2DetectsPosixGid(): void
    {
        // Generate a valid UuidV2
        $uuid = (string) UuidV2::generate(1234, 'uid');
        $hex = str_replace('-', '', $uuid);

        // Force domain byte to "01" for POSIX GID
        $modifiedHex = substr_replace($hex, '01', 18, 2);
        $modifiedUuid = substr($modifiedHex, 0, 8) . '-'
            . substr($modifiedHex, 8, 4) . '-'
            . substr($modifiedHex, 12, 4) . '-'
            . substr($modifiedHex, 16, 4) . '-'
            . substr($modifiedHex, 20);

        $metadata = Inspector::analyze($modifiedUuid)->metadata();
        $this->assertSame('POSIX GID', $metadata['domain']);
    }

    /**
     * @throws RandomException
     */
    public function test_parseV2DetectsUnknownDomain(): void
    {
        $uuid = (string) UuidV2::generate(1234, 'uid');
        $hex = str_replace('-', '', $uuid);

        // Force domain byte to something unexpected like '05'
        $modifiedHex = substr_replace($hex, '05', 18, 2);
        $modifiedUuid = substr($modifiedHex, 0, 8) . '-'
            . substr($modifiedHex, 8, 4) . '-'
            . substr($modifiedHex, 12, 4) . '-'
            . substr($modifiedHex, 16, 4) . '-'
            . substr($modifiedHex, 20);

        $metadata = Inspector::analyze($modifiedUuid)->metadata();
        $this->assertSame('Unknown domain', $metadata['domain']);
    }

}
