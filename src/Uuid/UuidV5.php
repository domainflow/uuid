<?php

declare(strict_types=1);

namespace DomainFlow\Uuid;

use DomainFlow\Uuid\Interface\UuidInterface;
use DomainFlow\Uuid\Trait\UuidMethodsTrait;
use InvalidArgumentException;
use RuntimeException;

/**
 * Implements a deterministic, name-based UUID (version 5) using SHA-1.
 */
final readonly class UuidV5 implements UuidInterface
{
    use UuidMethodsTrait;

    private const string UUID_V5_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const string UUID_V5_NAMESPACE_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private string $uuid;

    /**
     * @param string $uuid The UUIDv5 string.
     * @throws InvalidArgumentException
     */
    private function __construct(
        string $uuid
    ) {
        if (!self::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UUIDv5: $uuid");
        }
        $this->uuid = strtolower($uuid);
    }

    /**
     * Generate a deterministic UUIDv5 using a namespace and name.
     *
     * @param string $namespace The namespace UUID (v5-compliant).
     * @param string $name Arbitrary name (e.g. email, filepath).
     * @return self
     */
    public static function generate(
        string $namespace,
        string $name
    ): self {
        return self::fromNamespaceAndName($namespace, $name);
    }

    /**
     * Creates a UUIDv5 from a namespace UUID and name.
     *
     * @param string $namespace The namespace UUID.
     * @param string $name The name string.
     * @throws InvalidArgumentException
     * @return self
     */
    public static function fromNamespaceAndName(
        string $namespace,
        string $name
    ): self {
        if (!preg_match(self::UUID_V5_NAMESPACE_REGEX, $namespace)) {
            throw new InvalidArgumentException("Invalid namespace UUID: $namespace");
        }

        $nsBytes = self::uuidToBytes($namespace);
        $hash = sha1($nsBytes . $name, true);

        $timeLow = bin2hex(substr($hash, 0, 4));
        $timeMid = bin2hex(substr($hash, 4, 2));

        $timeHiUnpack = unpack('n', substr($hash, 6, 2)) ?: [1 => 0];
        $timeHi = ($timeHiUnpack[1] & 0x0fff) | 0x5000;

        $clockSeqUnpack = unpack('n', substr($hash, 8, 2)) ?: [1 => 0];
        $clockSeq = ($clockSeqUnpack[1] & 0x3fff) | 0x8000;

        $node = bin2hex(substr($hash, 10, 6));

        $uuid = sprintf(
            '%s-%s-%04x-%04x-%s',
            $timeLow,
            $timeMid,
            $timeHi,
            $clockSeq,
            $node
        );

        return new self($uuid);
    }

    /**
     * Validates if a given string is a valid UUIDv5.
     *
     * @param string $uuid
     * @return bool
     */
    public static function isValid(
        string $uuid
    ): bool {
        return preg_match(
            self::UUID_V5_REGEX,
            $uuid
        ) === 1;
    }

    /**
     * Converts a UUID string to its binary representation.
     *
     * @param string $uuid The UUID string.
     * @throws RuntimeException
     * @return string The binary representation.
     */
    private static function uuidToBytes(
        string $uuid
    ): string {
        $result = @hex2bin(str_replace('-', '', $uuid));

        if ($result === false) {
            throw new RuntimeException("Failed to convert UUID to bytes: $uuid");
        }

        return $result;
    }
}
