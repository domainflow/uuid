<?php

declare(strict_types=1);

namespace DomainFlow\Uuid;

use DomainFlow\Uuid\Interface\UuidInterface;
use DomainFlow\Uuid\Trait\UuidMethodsTrait;
use InvalidArgumentException;
use Random\RandomException;

/**
 * Implements a UUIDv7 (version 7) using a timestamp and random bits.
 */
final readonly class UuidV7 implements UuidInterface
{
    use UuidMethodsTrait;

    private const string UUID_V7_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private string $uuid;

    /**
     * @param string $uuid The UUIDv7 string.
     * @throws InvalidArgumentException
     */
    private function __construct(
        string $uuid
    ) {
        if (!self::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UUIDv7: $uuid");
        }

        $this->uuid = strtolower($uuid);
    }

    /**
     * Generate a new UUIDv7.
     *
     * @throws RandomException
     * @return self
     */
    public static function generate(): self
    {
        $unixMillis = (int) (microtime(true) * 1000);
        $timeHex = str_pad(dechex($unixMillis), 12, '0', STR_PAD_LEFT);

        $timeLow = substr($timeHex, 0, 8);
        $timeMid = substr($timeHex, 8, 4);

        $randBytes = random_bytes(10); // 80 bits = 20 hex chars
        $randHex = bin2hex($randBytes);

        $timeHigh = hexdec(substr($randHex, 0, 4));
        $timeHigh = ($timeHigh & 0x0fff) | 0x7000; // UUIDv7
        $timeHighHex = str_pad(dechex($timeHigh), 4, '0', STR_PAD_LEFT);

        $clockSeqHi = hexdec(substr($randHex, 4, 2));
        $clockSeqHi = ($clockSeqHi & 0x3f) | 0x80; // variant
        $clockSeqHiHex = str_pad(dechex($clockSeqHi), 2, '0', STR_PAD_LEFT);

        $clockSeqLow = substr($randHex, 6, 2);
        $node = substr($randHex, 8, 12); // → now safe since we have 20+ hex chars

        $uuid = sprintf(
            '%s-%s-%s-%s%s-%s',
            $timeLow,
            $timeMid,
            $timeHighHex,
            $clockSeqHiHex,
            $clockSeqLow,
            $node
        );

        return new self($uuid);
    }

    /**
     * Check if a string is a valid UUIDv7.
     *
     * @param string $uuid
     * @return bool
     */
    public static function isValid(
        string $uuid
    ): bool {
        return preg_match(self::UUID_V7_REGEX, $uuid) === 1;
    }
}
