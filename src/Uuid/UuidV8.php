<?php

declare(strict_types=1);

namespace DomainFlow\Uuid;

use DomainFlow\Uuid\Interface\UuidInterface;
use DomainFlow\Uuid\Trait\UuidMethodsTrait;
use InvalidArgumentException;

use Random\RandomException;

/**
 * Implements a UUIDv8 (version 8) using random bits.
 */
final readonly class UuidV8 implements UuidInterface
{
    use UuidMethodsTrait;

    private const string UUID_V8_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-8[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private string $uuid;

    /**
     * @param string $uuid The UUIDv8 string.
     * @throws InvalidArgumentException
     */
    private function __construct(string $uuid)
    {
        if (!self::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UUIDv8: $uuid");
        }

        $this->uuid = strtolower($uuid);
    }

    /**
     * Generate a new UUIDv8.
     *
     * @throws RandomException
     * @return self
     */
    public static function generate(): self
    {
        $bytes = random_bytes(16);
        $hex = bin2hex($bytes);

        // Insert version (8) into bits 12-15 of the UUID (i.e., hex[12] & hex[13])
        $hex[12] = '8';

        // Set the variant (10xx) in the relevant position (hex[16] in the hex string)
        $clockSeqHi = hexdec($hex[16]);
        $clockSeqHi = ($clockSeqHi & 0x3f) | 0x80;
        $hex[16] = dechex($clockSeqHi >> 4);
        $hex[17] = dechex($clockSeqHi & 0xf);

        $uuid = sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );

        return new self($uuid);
    }

    /**
     * Check if a string is a valid UUIDv8.
     *
     * @param string $uuid The UUIDv8 string.
     * @return bool
     */
    public static function isValid(
        string $uuid
    ): bool {
        return preg_match(self::UUID_V8_REGEX, $uuid) === 1;
    }
}
