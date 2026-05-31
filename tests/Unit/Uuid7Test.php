<?php

declare(strict_types=1);

namespace Kissous\Uuid7\Tests\Unit;

use Kissous\Uuid7\Exception\InvalidUuidException;
use Kissous\Uuid7\Uuid7;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Uuid7::class)]
final class Uuid7Test extends TestCase
{
    private const string PATTERN =
        '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    #[Test]
    public function generate_returns_a_well_formed_uuidv7(): void
    {
        $uuid = Uuid7::generate();

        self::assertMatchesRegularExpression(self::PATTERN, $uuid->toString());
    }

    #[Test]
    public function generate_sets_version_7_and_rfc_variant(): void
    {
        $uuid = Uuid7::generate()->toString();

        // 15th char = version, 20th = variant (groups separated by '-')
        self::assertSame('7', $uuid[14], 'the version nibble must equal 7');
        self::assertContains($uuid[19], ['8', '9', 'a', 'b'], 'RFC 9562 variant expected');
    }

    #[Test]
    public function generate_produces_unique_values(): void
    {
        $a = Uuid7::generate()->toString();
        $b = Uuid7::generate()->toString();

        self::assertNotSame($a, $b);
    }

    #[Test]
    public function from_string_round_trips_through_to_string(): void
    {
        $canonical = '0190f3a4-7b2c-7def-8123-456789abcdef';

        self::assertSame($canonical, Uuid7::fromString($canonical)->toString());
    }

    #[Test]
    public function from_string_is_case_insensitive_and_normalizes_to_lowercase(): void
    {
        $upper = '0190F3A4-7B2C-7DEF-8123-456789ABCDEF';

        self::assertSame(strtolower($upper), Uuid7::fromString($upper)->toString());
    }

    /**
     * @param non-empty-string $invalid
     */
    #[Test]
    #[DataProvider('invalidStrings')]
    public function from_string_rejects_invalid_input(string $invalid): void
    {
        $this->expectException(InvalidUuidException::class);

        Uuid7::fromString($invalid);
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function invalidStrings(): iterable
    {
        yield 'empty string' => [' '];
        yield 'too short' => ['0190f3a4-7b2c-7def-8123'];
        yield 'non-hex character' => ['0190f3a4-7b2c-7def-8123-456789abcdez'];
        yield 'wrong version' => ['0190f3a4-7b2c-4def-8123-456789abcdef'];
        yield 'wrong variant' => ['0190f3a4-7b2c-7def-c123-456789abcdef'];
        yield 'no hyphens' => ['0190f3a47b2c7def8123456789abcdef'];
    }

    #[Test]
    public function casting_to_string_matches_to_string(): void
    {
        $uuid = Uuid7::generate();

        self::assertSame($uuid->toString(), (string) $uuid);
    }
}
