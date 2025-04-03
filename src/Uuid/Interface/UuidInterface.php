<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Interface;

use JsonSerializable;

interface UuidInterface extends JsonSerializable
{
    /**
     * Create a new UUID object from a string
     *
     * @param string $uuid
     * @return static
     */
    public static function fromString(string $uuid): static;

    /**
     * Check if a string is a valid UUID
     *
     * @param string $uuid
     * @return bool
     */
    public static function isValid(string $uuid): bool;

    /**
     * Check if the UUID is equal to another UUID (from the same class)
     *
     * @param UuidInterface $other
     * @return bool
     */
    public function equals(self $other): bool;

    /**
     * Convert the UUID to a string
     *
     * @return string
     */
    public function __toString(): string;
}
