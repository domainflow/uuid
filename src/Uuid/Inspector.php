<?php

declare(strict_types=1);

namespace DomainFlow\Uuid;

use InvalidArgumentException;

/**
 * Analyzes a UUID string, auto-detecting its version and variant,
 * and extracts available metadata based on the UUID version.
 */
class Inspector
{
    private string $uuid;
    private int $version;
    private string $variant;

    /**
     * @param string $uuid The normalized UUID string.
     * @param int $version The detected UUID version.
     * @param string $variant The detected UUID variant.
     */
    private function __construct(
        string $uuid,
        int $version,
        string $variant
    ) {
        $this->uuid = $uuid;
        $this->version = $version;
        $this->variant = $variant;
    }

    /**
     * Analyzes a UUID string and returns an Inspector instance.
     *
     * @param string $uuid The UUID string to analyze.
     * @throws InvalidArgumentException
     * @return self
     */
    public static function analyze(
        string $uuid
    ): self {
        $clean = strtolower(trim($uuid));

        // Relaxed regex: allow any hex digit in the variant byte for analysis.
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/', $clean)) {
            throw new InvalidArgumentException("Invalid UUID format: $uuid");
        }

        // Force version to int (hexdec can return float for large numbers)
        $version = (int) hexdec($clean[14]);

        $hex = str_replace('-', '', $clean);
        $variantByte = hexdec(substr($hex, 16, 2));

        $variant = match (true) {
            ($variantByte & 0xC0) === 0x80 => 'RFC 4122',
            ($variantByte & 0xE0) === 0xC0 => 'Microsoft Reserved',
            ($variantByte & 0x80) === 0x00 => 'NCS Compatibility',
            default => 'Unknown',
        };

        return new self($clean, $version, $variant);
    }

    /**
     * Returns the detected UUID version.
     *
     * @return int
     */
    public function version(): int
    {
        return $this->version;
    }

    /**
     * Returns the detected UUID variant.
     *
     * @return string
     */
    public function variant(): string
    {
        return $this->variant;
    }

    /**
     * Returns metadata extracted from the UUID.
     *
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return match ($this->version) {
            1 => $this->parseV1(),
            2 => $this->parseV2(),
            3 => $this->basic('MD5'),
            4 => $this->random(),
            5 => $this->basic('SHA-1'),
            6 => $this->parseV6(),
            7 => $this->parseV7(),
            8 => ['info' => 'UUIDv8 is application-defined, structure unknown'],
            default => ['error' => 'Unknown UUID version'],
        };
    }

    /**
     * Extracts metadata from a UUIDv1.
     *
     * @return array<string, mixed>
     */
    private function parseV1(): array
    {
        // Extract timestamp (60 bits), clock sequence, and node from UUIDv1
        $hex = str_replace('-', '', $this->uuid);

        $timeLow = substr($hex, 0, 8);
        $timeMid = substr($hex, 8, 4);
        $timeHi = substr($hex, 12, 4);
        $clockSeq = substr($hex, 16, 4);
        $node = substr($hex, 20, 12);

        // Reconstruct timestamp: remove version nibble from timeHi and prepend it to timeMid and timeLow
        $timestampHex = substr($timeHi, 1) . $timeMid . $timeLow;
        $timestamp = hexdec($timestampHex);

        $clockSeqVal = hexdec($clockSeq);
        $nodeFormatted = strtoupper(implode(':', str_split($node, 2)));

        return [
            'timestamp_100ns' => $timestamp,
            'clock_sequence' => $clockSeqVal,
            'node' => $nodeFormatted,
        ];
    }

    /**
     * Extracts metadata from a UUIDv2.
     *
     * @return array<string, mixed>
     */
    private function parseV2(): array
    {
        $hex = str_replace('-', '', $this->uuid);
        $domain = hexdec(substr($hex, 18, 2));
        $domainType = match ($domain) {
            0 => 'POSIX UID',
            1 => 'POSIX GID',
            default => 'Unknown domain',
        };

        $localId = hexdec(substr($hex, 0, 8));

        return [
            'domain' => $domainType,
            'local_identifier' => $localId,
            'note' => 'UUIDv2 replaces timestamp low bits with a UID/GID',
        ];
    }

    /**
     * Extracts sortable timestamp metadata from a UUIDv6.
     *
     * @return array<string, mixed>
     */
    private function parseV6(): array
    {
        $hex = str_replace('-', '', $this->uuid);
        $timeHigh = substr($hex, 0, 8);
        $timeMid = substr($hex, 8, 4);
        $timeLow = substr($hex, 12, 4);

        $timestampHex = substr($timeHigh, 1) . $timeMid . $timeLow;
        $timestamp = hexdec($timestampHex);

        return [
            'timestamp_100ns' => $timestamp,
            'sortable' => true,
        ];
    }

    /**
     * Extracts metadata from a UUIDv7.
     *
     * @return array<string, mixed>
     */
    private function parseV7(): array
    {
        // UUIDv7 stores timestamp as unix milliseconds in the first 48 bits.
        $parts = explode('-', $this->uuid);
        $timestampHex = substr($parts[0], 0, 8) . substr($parts[1], 0, 4);
        $unixMillis = hexdec($timestampHex);

        return [
            'unix_timestamp_ms' => $unixMillis,
            'sortable' => true,
        ];
    }

    /**
     * Returns basic metadata for UUIDv3 or UUIDv5.
     *
     * @param string $hashType The hash algorithm used (e.g. MD5 or SHA-1).
     * @return array<string, mixed>
     */
    private function basic(
        string $hashType
    ): array {
        return [
            'hash_type' => $hashType,
            'deterministic' => true,
            'note' => "UUIDv{$this->version} encodes a hash of namespace + name",
        ];
    }

    /**
     * Returns metadata for a random UUID (v4).
     *
     * @return array<string, mixed>
     */
    private function random(): array
    {
        return [
            'entropy_source' => 'Cryptographic random bytes',
            'deterministic' => false,
            'note' => 'UUIDv4 contains no embedded metadata',
        ];
    }
}
