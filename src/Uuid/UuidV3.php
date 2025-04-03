<?php

declare(strict_types=1);

namespace DomainFlow\Uuid;

use DomainFlow\Uuid\Interface\UuidInterface;
use DomainFlow\Uuid\Trait\UuidMethodsTrait;
use InvalidArgumentException;

/**
 * Implements a deterministic, name-based UUID (version 3) using MD5.
 */
final readonly class UuidV3 implements UuidInterface
{
    use UuidMethodsTrait;

    private const string UUID_V3_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-3[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const string UUID_V3_NAMESPACE_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private string $uuid;

    /**
     * @param string $uuid
     * @throws InvalidArgumentException
     */
    private function __construct(
        string $uuid
    ) {
        if (!self::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UUIDv3: $uuid");
        }
        $this->uuid = strtolower($uuid);
    }

    /**
     * Generate a deterministic UUIDv3 using a namespace and a name.
     *
     * @param string $namespace The namespace UUID (v3-compliant).
     * @param string $name The name from which to generate the UUID.
     * @return self
     */
    public static function generate(
        string $namespace,
        string $name
    ): self {
        return self::fromNamespaceAndName($namespace, $name);
    }

    /**
     * Creates a UUIDv3 from a namespace UUID and a name.
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
        if (!preg_match(self::UUID_V3_NAMESPACE_REGEX, $namespace)) {
            throw new InvalidArgumentException("Invalid namespace UUID: $namespace");
        }

        $nsBytes = hex2bin(str_replace('-', '', $namespace));

        $hash = md5($nsBytes . $name, true);

        $timeLow = bin2hex(substr($hash, 0, 4));
        $timeMid = bin2hex(substr($hash, 4, 2));

        $timeHiUnpack = unpack('n', substr($hash, 6, 2)) ?: [1 => 0];
        $timeHi = ($timeHiUnpack[1] & 0x0fff) | 0x3000;

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
     * Validates if a given string is a valid UUIDv3.
     *
     * @param string $uuid
     * @return bool
     */
    public static function isValid(
        string $uuid
    ): bool {
        return preg_match(self::UUID_V3_REGEX, $uuid) === 1;
    }
}
