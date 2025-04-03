<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Trait;

use DomainFlow\Uuid\Interface\UuidInterface;
use JsonException;

/**
 * @mixin UuidInterface
 */
trait UuidMethodsTrait
{
    /**
     * Compares this UUID with another for equality.
     *
     * @param UuidInterface  $other
     * @return bool
     */
    public function equals(
        UuidInterface $other
    ): bool {
        return (string) $this === (string) $other;
    }

    /**
     * Returns the UUIDv1 as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->uuid;
    }

    /**
     * Serializes the UUIDv1 for JSON encoding.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->uuid;
    }

    /**
     * Creates a UUIDv1 from a string.
     *
     * @param string $uuid
     * @return static
     */
    public static function fromString(
        string $uuid
    ): static {
        return new self($uuid);
    }

    /**
     * Creates a UUIDv1 from a JSON string.
     *
     * @param string $json
     * @throws JsonException
     * @return self
     */
    public static function fromJson(
        string $json
    ): static {

        if (!json_validate($json)) {
            throw new JsonException('Invalid JSON string');
        }

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_string($decoded)) {
            throw new JsonException('Invalid JSON: expected a string');
        }

        return static::fromString($decoded);
    }
}
