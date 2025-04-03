<?php

declare(strict_types=1);

namespace DomainFlow\Uuid;

use DomainFlow\Uuid\Interface\UuidInterface;
use DomainFlow\Uuid\Trait\UuidMethodsTrait;
use InvalidArgumentException;
use Random\RandomException;

/**
 * Implements a random-based UUID (version 2).
 */
final readonly class UuidV2 implements UuidInterface
{
    use UuidMethodsTrait;

    private const string UUID_V2_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-2[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private string $uuid;

    /**
     * @param string $uuid The UUIDv2 string.
     * @throws InvalidArgumentException
     */
    private function __construct(
        string $uuid
    ) {
        if (!self::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UUIDv2: $uuid");
        }

        $this->uuid = strtolower($uuid);
    }

    /**
     * Generate a UUIDv2 using DCE domain and local identifier.
     *
     * @param int $localId The local identifier (UID or GID).
     * @param string $domain The domain (UID or GID).
     * @throws RandomException
     * @return self
     */
    public static function generate(
        int $localId,
        string $domain = 'uid'
    ): self {
        $domainValue = match (strtolower($domain)) {
            'uid' => 0,
            'gid' => 1,
            default => throw new InvalidArgumentException("Invalid domain: $domain"),
        };

        $timestamp = self::getTimestamp();
        $timeLow = ($localId & 0xFFFFFFFF); // Replace lower 32 bits of timestamp with UID/GID
        $timeMid = ($timestamp >> 32) & 0xFFFF;
        $timeHigh = ($timestamp >> 48) & 0x0FFF;
        $timeHigh |= 0x2000; // Set version to 2

        $clockSeq = random_int(0, 0x3FFF);
        $clockSeq |= 0x8000; // RFC variant

        $node = bin2hex(random_bytes(6));

        $uuid = sprintf(
            '%08x-%04x-%04x-%04x-%02x%s',
            $timeLow,
            $timeMid,
            $timeHigh,
            $clockSeq,
            $domainValue,
            substr($node, 2) // Skip first byte (used for domain)
        );

        return new self($uuid);
    }

    /**
     * Generate a UUIDv2 using a POSIX UID.
     *
     * @return int
     */
    private static function getTimestamp(): int
    {
        // Number of 100-nanosecond intervals between the UUID epoch (1582-10-15)
        // and Unix epoch (1970-01-01). Used to convert Unix time to UUID timestamp format.
        $gregorianOffset = 0x01B21DD213814000;
        $time = (int) (microtime(true) * 1_000_000);

        return $time * 10 + $gregorianOffset;
    }

    /**
     * Validates a UUIDv2.
     *
     * @param string $uuid
     * @return bool
     */
    public static function isValid(
        string $uuid
    ): bool {
        return preg_match(self::UUID_V2_REGEX, $uuid) === 1;
    }

}
