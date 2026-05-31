<?php

declare(strict_types=1);

namespace Kissous\Uuid7;

use Kissous\Uuid7\Exception\InvalidUuidException;
use Stringable;

/**
 * UUIDv7 identifier (RFC 9562): an immutable, time-ordered value object.
 *
 * 128-bit layout:
 *   - 48 bits: Unix timestamp in milliseconds (big-endian)
 *   -  4 bits: version (0b0111 = 7)
 *   - 12 bits: random
 *   -  2 bits: variant (0b10)
 *   - 62 bits: random
 */
final class Uuid7 implements Stringable
{
    /**
     * Canonical form: 8-4-4-4-12, version 7, RFC 9562 variant.
     */
    private const string PATTERN =
        '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * @param non-empty-string $bytes 16 raw bytes
     */
    private function __construct(
        private readonly string $bytes,
    ) {
    }

    /**
     * Generates a new UUIDv7 from the system clock and `random_bytes()`.
     */
    public static function generate(): self
    {
        $bytes = random_bytes(16);
        $timeMs = (int) (microtime(true) * 1000);

        // 48-bit timestamp, big-endian, in bytes 0..5.
        $bytes[0] = \chr(($timeMs >> 40) & 0xff);
        $bytes[1] = \chr(($timeMs >> 32) & 0xff);
        $bytes[2] = \chr(($timeMs >> 24) & 0xff);
        $bytes[3] = \chr(($timeMs >> 16) & 0xff);
        $bytes[4] = \chr(($timeMs >> 8) & 0xff);
        $bytes[5] = \chr($timeMs & 0xff);

        // Version 7 in the high nibble of byte 6.
        $bytes[6] = \chr((\ord($bytes[6]) & 0x0f) | 0x70);
        // RFC 9562 variant (0b10) in the high bits of byte 8.
        $bytes[8] = \chr((\ord($bytes[8]) & 0x3f) | 0x80);

        return new self($bytes);
    }

    /**
     * Builds a UUIDv7 from its canonical form (case-insensitive).
     *
     * @throws InvalidUuidException if the string is not a valid UUIDv7
     */
    public static function fromString(string $uuid): self
    {
        if (preg_match(self::PATTERN, $uuid) !== 1) {
            throw InvalidUuidException::forString($uuid);
        }

        $bytes = hex2bin(str_replace('-', '', strtolower($uuid)));
        \assert($bytes !== false && $bytes !== '');

        return new self($bytes);
    }

    /**
     * Canonical form, normalized to lowercase (8-4-4-4-12).
     */
    public function toString(): string
    {
        $hex = bin2hex($this->bytes);

        return \sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
