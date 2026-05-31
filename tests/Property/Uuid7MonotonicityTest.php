<?php

declare(strict_types=1);

namespace Kissous\Uuid7\Tests\Property;

use Kissous\Uuid7\Uuid7;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Property-based checks over a large batch of generated UUIDs.
 *
 * Canonical UUIDs share a fixed layout (hyphens at constant positions), so a
 * plain lexicographic string comparison reflects the underlying 128-bit order.
 */
#[CoversClass(Uuid7::class)]
final class Uuid7MonotonicityTest extends TestCase
{
    private const int SAMPLE_SIZE = 50_000;

    /**
     * @return list<string>
     */
    private static function generateBatch(int $count): array
    {
        $out = [];
        for ($i = 0; $i < $count; ++$i) {
            $out[] = Uuid7::generate()->toString();
        }

        return $out;
    }

    #[Test]
    public function generated_values_are_strictly_increasing(): void
    {
        $batch = self::generateBatch(self::SAMPLE_SIZE);

        // A tight loop produces many UUIDs within the same millisecond; each
        // must still be strictly greater than the previous one.
        for ($i = 1; $i < self::SAMPLE_SIZE; ++$i) {
            self::assertGreaterThan(
                $batch[$i - 1],
                $batch[$i],
                "UUID at index {$i} is not strictly greater than its predecessor",
            );
        }
    }

    #[Test]
    public function generated_values_are_unique(): void
    {
        $batch = self::generateBatch(self::SAMPLE_SIZE);

        self::assertCount(self::SAMPLE_SIZE, array_unique($batch));
    }

    #[Test]
    public function every_generated_value_is_a_well_formed_uuidv7(): void
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

        foreach (self::generateBatch(self::SAMPLE_SIZE) as $uuid) {
            self::assertMatchesRegularExpression($pattern, $uuid);
        }
    }
}
