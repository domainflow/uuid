<?php

declare(strict_types=1);

namespace DomainFlow\Uuid;

use DomainFlow\Uuid\Interface\UuidInterface;
use DomainFlow\Uuid\Trait\UuidMethodsTrait;
use InvalidArgumentException;
use Random\RandomException;

/**
 * Implements a random UUID (version 4).
 */
final readonly class UuidV4 implements UuidInterface
{
    use UuidMethodsTrait;

    private const string UUID_V4_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private string $uuid;

    /**
     * @param string $uuid The UUIDv4 string.
     * @throws InvalidArgumentException
     */
    private function __construct(
        string $uuid
    ) {
        if (!self::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UUIDv4: $uuid");
        }

        $this->uuid = strtolower($uuid);
    }

    /**
     * Generate a new UUIDv4.
     *
     * @throws RandomException
     * @return self
     */
    public static function generate(): self
    {
        $bytes = random_bytes(16);

        // Set version (4) in byte 6
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);

        // Set variant (RFC 4122) in byte 8
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        $hex = bin2hex($bytes);

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
     * Validates a UUIDv4.
     *
     * @param string $uuid
     * @return bool
     */
    public static function isValid(
        string $uuid
    ): bool {
        return preg_match(self::UUID_V4_REGEX, $uuid) === 1;
    }
}
