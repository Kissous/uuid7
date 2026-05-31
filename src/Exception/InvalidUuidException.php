<?php

declare(strict_types=1);

namespace Kissous\Uuid7\Exception;

use InvalidArgumentException;

final class InvalidUuidException extends InvalidArgumentException
{
    public static function forString(string $value): self
    {
        return new self(\sprintf('"%s" n\'est pas un UUIDv7 valide (RFC 9562).', $value));
    }
}
