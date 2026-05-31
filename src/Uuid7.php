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
 *   - 12 bits: random (rand_a)
 *   -  2 bits: variant (0b10)
 *   - 62 bits: random (rand_b)
 *
 * Generation is monotonic: two UUIDs produced within the same millisecond are
 * strictly increasing. This is achieved with the RFC 9562 "monotonic random"
 * method — the 74-bit (rand_a, rand_b) field is seeded randomly on each new
 * millisecond and incremented as a counter for subsequent calls in that ms.
 *
 * The monotonic counter lives in process-local static state. PHP is
 * shared-nothing per request, so ordering is guaranteed within a single
 * process, not across separate processes (which is fine: their timestamps
 * already separate them, and collisions across 74 random bits are negligible).
 */
final class Uuid7 implements Stringable
{
    /**
     * Canonical form: 8-4-4-4-12, version 7, RFC 9562 variant.
     */
    private const string PATTERN =
        '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /** Largest value for the 12-bit rand_a field. */
    private const int MAX_RAND_A = 0xFFF;

    /** Largest value for the 62-bit rand_b field. */
    private const int MAX_RAND_B = (1 << 62) - 1;

    /** Timestamp (ms) of the last generated UUID, or -1 if none yet. */
    private static int $lastMs = -1;

    /** rand_a (12 bits) of the last generated UUID. */
    private static int $lastRandA = 0;

    /** rand_b (62 bits) of the last generated UUID. */
    private static int $lastRandB = 0;

    /**
     * @param non-empty-string $bytes 16 raw bytes
     */
    private function __construct(
        private readonly string $bytes,
    ) {
    }

    /**
     * Generates a new, monotonic UUIDv7 from the system clock and `random_bytes()`.
     */
    public static function generate(): self
    {
        $ms = (int) (microtime(true) * 1000);

        if ($ms > self::$lastMs) {
            // A later millisecond: draw fresh randomness.
            [$randA, $randB] = self::randomFields();
        } else {
            // Same millisecond, or the clock moved backwards: stay strictly
            // increasing by reusing the previous timestamp and bumping the
            // 74-bit (rand_a, rand_b) counter.
            $ms = self::$lastMs;
            $randA = self::$lastRandA;
            $randB = self::$lastRandB + 1;

            if ($randB > self::MAX_RAND_B) {
                $randB = 0;
                ++$randA;

                if ($randA > self::MAX_RAND_A) {
                    // 74 bits exhausted in a single ms (not reachable in
                    // practice): roll over into the next millisecond.
                    $ms = self::$lastMs + 1;
                    [$randA, $randB] = self::randomFields();
                }
            }
        }

        self::$lastMs = $ms;
        self::$lastRandA = $randA;
        self::$lastRandB = $randB;

        return new self(self::compose($ms, $randA, $randB));
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

    /**
     * Draws a fresh (rand_a, rand_b) pair from `random_bytes()`.
     *
     * @return array{int, int} rand_a (12 bits), rand_b (62 bits)
     */
    private static function randomFields(): array
    {
        $raw = random_bytes(10);

        $randA = ((\ord($raw[0]) << 8) | \ord($raw[1])) & self::MAX_RAND_A;

        $randB = 0;
        for ($i = 2; $i < 10; ++$i) {
            $randB = ($randB << 8) | \ord($raw[$i]);
        }
        $randB &= self::MAX_RAND_B;

        return [$randA, $randB];
    }

    /**
     * Assembles the 16 raw bytes from the timestamp and random fields.
     *
     * @return non-empty-string
     */
    private static function compose(int $ms, int $randA, int $randB): string
    {
        $bytes = str_repeat("\0", 16);

        // 48-bit timestamp, big-endian, in bytes 0..5.
        $bytes[0] = \chr(($ms >> 40) & 0xff);
        $bytes[1] = \chr(($ms >> 32) & 0xff);
        $bytes[2] = \chr(($ms >> 24) & 0xff);
        $bytes[3] = \chr(($ms >> 16) & 0xff);
        $bytes[4] = \chr(($ms >> 8) & 0xff);
        $bytes[5] = \chr($ms & 0xff);

        // Byte 6: version 7 (high nibble) + top 4 bits of rand_a.
        $bytes[6] = \chr(0x70 | (($randA >> 8) & 0x0f));
        // Byte 7: low 8 bits of rand_a.
        $bytes[7] = \chr($randA & 0xff);

        // Byte 8: variant 0b10 (top 2 bits) + top 6 bits of rand_b.
        $bytes[8] = \chr(0x80 | (($randB >> 56) & 0x3f));
        // Bytes 9..15: remaining 56 bits of rand_b.
        $bytes[9] = \chr(($randB >> 48) & 0xff);
        $bytes[10] = \chr(($randB >> 40) & 0xff);
        $bytes[11] = \chr(($randB >> 32) & 0xff);
        $bytes[12] = \chr(($randB >> 24) & 0xff);
        $bytes[13] = \chr(($randB >> 16) & 0xff);
        $bytes[14] = \chr(($randB >> 8) & 0xff);
        $bytes[15] = \chr($randB & 0xff);

        return $bytes;
    }
}
