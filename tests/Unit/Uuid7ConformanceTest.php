<?php

declare(strict_types=1);

namespace Kissous\Uuid7\Tests\Unit;

use Kissous\Uuid7\Uuid7;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Conformance against the example UUIDv7 published in RFC 9562.
 *
 * Reference value: 017F22E2-79B0-7CC3-98C4-DC0C0C07398F, whose 48-bit timestamp
 * encodes 2022-02-22 14:22:22 GMT-05:00 (= 19:22:22 UTC), i.e. 1645557742000 ms.
 */
#[CoversClass(Uuid7::class)]
final class Uuid7ConformanceTest extends TestCase
{
    private const string RFC_EXAMPLE = '017f22e2-79b0-7cc3-98c4-dc0c0c07398f';
    private const int RFC_EXAMPLE_TS_MS = 1_645_557_742_000;

    #[Test]
    public function rfc_example_round_trips(): void
    {
        self::assertSame(
            self::RFC_EXAMPLE,
            Uuid7::fromString(self::RFC_EXAMPLE)->toString(),
        );
    }

    #[Test]
    public function rfc_example_is_accepted_case_insensitively(): void
    {
        self::assertSame(
            self::RFC_EXAMPLE,
            Uuid7::fromString(strtoupper(self::RFC_EXAMPLE))->toString(),
        );
    }

    #[Test]
    public function rfc_example_has_version_7_and_rfc_variant(): void
    {
        $uuid = Uuid7::fromString(self::RFC_EXAMPLE)->toString();

        self::assertSame('7', $uuid[14], 'version nibble');
        self::assertContains($uuid[19], ['8', '9', 'a', 'b'], 'variant nibble');
    }

    #[Test]
    public function rfc_example_encodes_the_expected_timestamp(): void
    {
        $uuid = Uuid7::fromString(self::RFC_EXAMPLE)->toString();

        // First 48 bits = Unix timestamp in milliseconds, i.e. the first 12 hex
        // characters once the hyphens are stripped.
        $timestampHex = substr(str_replace('-', '', $uuid), 0, 12);

        self::assertSame(self::RFC_EXAMPLE_TS_MS, (int) hexdec($timestampHex));
    }
}
