<?php

declare(strict_types=1);

namespace Kissous\Uuid7;

use Kissous\Uuid7\Exception\InvalidUuidException;
use Stringable;

/**
 * Identifiant UUIDv7 (RFC 9562) : value object immuable et time-ordered.
 *
 * Disposition des 128 bits :
 *   - 48 bits : timestamp Unix en millisecondes (big-endian)
 *   -  4 bits : version (0b0111 = 7)
 *   - 12 bits : aléa
 *   -  2 bits : variant (0b10)
 *   - 62 bits : aléa
 */
final class Uuid7 implements Stringable
{
    /**
     * Forme canonique : 8-4-4-4-12, version 7, variant RFC 9562.
     */
    private const string PATTERN =
        '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * @param non-empty-string $bytes 16 octets bruts
     */
    private function __construct(
        private readonly string $bytes,
    ) {
    }

    /**
     * Génère un nouvel UUIDv7 à partir de l'horloge système et de `random_bytes()`.
     */
    public static function generate(): self
    {
        $bytes = random_bytes(16);
        $timeMs = (int) (microtime(true) * 1000);

        // 48 bits de timestamp, big-endian, sur les octets 0..5.
        $bytes[0] = \chr(($timeMs >> 40) & 0xff);
        $bytes[1] = \chr(($timeMs >> 32) & 0xff);
        $bytes[2] = \chr(($timeMs >> 24) & 0xff);
        $bytes[3] = \chr(($timeMs >> 16) & 0xff);
        $bytes[4] = \chr(($timeMs >> 8) & 0xff);
        $bytes[5] = \chr($timeMs & 0xff);

        // Version 7 sur le quartet haut de l'octet 6.
        $bytes[6] = \chr((\ord($bytes[6]) & 0x0f) | 0x70);
        // Variant RFC 9562 (0b10) sur les bits hauts de l'octet 8.
        $bytes[8] = \chr((\ord($bytes[8]) & 0x3f) | 0x80);

        return new self($bytes);
    }

    /**
     * Construit un UUIDv7 depuis sa forme canonique (insensible à la casse).
     *
     * @throws InvalidUuidException si la chaîne n'est pas un UUIDv7 valide
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
     * Forme canonique normalisée en minuscules (8-4-4-4-12).
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
