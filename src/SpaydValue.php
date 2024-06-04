<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Stringable;

/**
 * Each normalizer must be lossless, like {@see SpaydValue::normalize()}
 *
 * @phpstan-type TSpaydValue = string union of supported types
 */
final class SpaydValue
{
    /**
     * @param TSpaydValue $value
     *
     * @throws Exception\CouldNotNormalizeValue if could not losslessly normalize the value
     */
    public static function normalize(?SpaydKey $key, mixed $value): string
    {
        return match ($key) {
            default => self::normalizeString($value),
        };
    }

    /**
     * @param TSpaydValue $value
     *
     * @throws Exception\CouldNotNormalizeValue
     */
    public static function normalizeString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        throw new Exception\CouldNotNormalizeValue(__METHOD__, $value);
    }
}
