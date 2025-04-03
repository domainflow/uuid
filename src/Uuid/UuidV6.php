<?php

declare(strict_types=1);

namespace DomainFlow\Uuid;

use DomainFlow\Uuid\Interface\UuidInterface;
use DomainFlow\Uuid\Trait\UuidMethodsTrait;
use InvalidArgumentException;
use Random\RandomException;

/**
 * Implements a UUIDv6 (version 6) using a timestamp and random bits.
 */
final readonly class UuidV6 implements UuidInterface
{
    use UuidMethodsTrait;

    private const string UUID_V6_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-6[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private string $uuid;

    /**
     * @param string $uuid The UUIDv6 string.
     * @throws InvalidArgumentException
     */
    private function __construct(
        string $uuid
    ) {
        if (!self::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UuidV6: $uuid");
        }

        $this->uuid = strtolower($uuid);
    }

    /**
     * Generate a new UuidV6.
     *
     * @throws RandomException
     * @return self
     */
    public static function generate(): self
    {
        // UuidV6 uses the same timestamp as v1: 100ns intervals since 1582-10-15
        $gregorianEpochOffset = 0x01B21DD213814000; // = 122192928000000000
        $time = (int) (microtime(true) * 1_000_000); // microseconds
        $uuidTime = $time * 10 + $gregorianEpochOffset;

        $timeHex = str_pad(dechex($uuidTime), 16, '0', STR_PAD_LEFT);
        $timeHigh = substr($timeHex, 0, 8);// reordered
        $timeMid = substr($timeHex, 8, 4);
        $timeLow = substr($timeHex, 12, 4); // contains version bits

        $timeLowInt = hexdec($timeLow);
        $timeLowInt = ($timeLowInt & 0x0fff) | 0x6000; // version 6
        $timeLow = str_pad(dechex($timeLowInt), 4, '0', STR_PAD_LEFT);

        $clockSeq = random_int(0, 0x3FFF); // 14-bit
        $clockSeqHi = ($clockSeq >> 8) & 0x3f | 0x80;  // variant bits: 10xxxxxx
        $clockSeqLow = $clockSeq & 0xff;

        $node = bin2hex(random_bytes(6)); // 48-bit node (usually MAC)

        $uuid = sprintf(
            '%s-%s-%s-%02x%02x-%s',
            $timeHigh,
            $timeMid,
            $timeLow,
            $clockSeqHi,
            $clockSeqLow,
            $node
        );

        return new self($uuid);
    }

    /**
     * Check if a string is a valid UUIDv6.
     *
     * @param string $uuid The UUIDv6 string.
     * @return bool
     */
    public static function isValid(
        string $uuid
    ): bool {
        return preg_match(self::UUID_V6_REGEX, $uuid) === 1;
    }
}
