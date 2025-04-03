<?php

declare(strict_types=1);

namespace DomainFlow\Uuid;

use DomainFlow\Uuid\Interface\UuidInterface;
use DomainFlow\Uuid\Trait\UuidMethodsTrait;
use InvalidArgumentException;
use Random\RandomException;

/**
 * Implements a random-based UUID (version 1).
 */
final readonly class UuidV1 implements UuidInterface
{
    use UuidMethodsTrait;

    private const string UUID_V1_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-1[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private string $uuid;

    /**
     * @param string $uuid
     * @throws InvalidArgumentException
     */
    private function __construct(
        string $uuid
    ) {
        if (!self::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UUIDv1: $uuid");
        }

        $this->uuid = strtolower($uuid);
    }

    /**
     * Generate a new UUIDv1.
     *
     * @throws RandomException
     * @return self
     */
    public static function generate(): self
    {
        // Time in 100ns since 1582-10-15 (UUID epoch)
        $gregorianOffset = 0x01B21DD213814000;
        $now = (int) (microtime(true) * 1_000_000); // microseconds
        $time = $now * 10 + $gregorianOffset;

        $timeHex = str_pad(dechex($time), 16, '0', STR_PAD_LEFT);
        $timeLow = substr($timeHex, 8, 8);
        $timeMid = substr($timeHex, 4, 4);
        $timeHiAndVersion = substr($timeHex, 0, 4);

        $timeHiAndVersionInt = hexdec($timeHiAndVersion);
        $timeHiAndVersionInt = ($timeHiAndVersionInt & 0x0fff) | 0x1000; // version 1
        $timeHiAndVersion = str_pad(dechex($timeHiAndVersionInt), 4, '0', STR_PAD_LEFT);

        $clockSeq = random_int(0, 0x3FFF);
        $clockSeqHi = ($clockSeq >> 8) & 0x3f | 0x80; // variant RFC4122
        $clockSeqLow = $clockSeq & 0xff;

        $node = bin2hex(random_bytes(6)); // typically MAC address

        $uuid = sprintf(
            '%s-%s-%s-%02x%02x-%s',
            $timeLow,
            $timeMid,
            $timeHiAndVersion,
            $clockSeqHi,
            $clockSeqLow,
            $node
        );

        return new self($uuid);
    }

    /**
     * Validates a UUIDv1 string.
     *
     * @param string $uuid
     * @return bool
     */
    public static function isValid(
        string $uuid
    ): bool {
        return preg_match(self::UUID_V1_REGEX, $uuid) === 1;
    }

}
