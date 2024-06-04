<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use DateTimeInterface;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

/**
 * @phpstan-type TSpaydValue = Currency|DateTimeInterface|Money|int|string
 */
final class SpaydValue
{
    /**
     * @param TSpaydValue $value
     *
     * @throws Exception\CouldNotConvertValue
     */
    public static function convert(?SpaydKey $key, mixed $value): string
    {
        return match ($key) {
            SpaydKey::Amount => self::convertMoneyAmount($value),
            SpaydKey::ConstantSymbol => self::convertInt($value),
            SpaydKey::CurrencyCode => self::convertMoneyCurrency($value),
            SpaydKey::DueDate => self::convertDate($value),
            SpaydKey::SpecificSymbol => self::convertInt($value),
            SpaydKey::VariableSymbol => self::convertInt($value),
            default => self::convertString($value, __METHOD__),
        };
    }

    /**
     * @param TSpaydValue $value
     *
     * @throws Exception\CouldNotConvertValue
     */
    public static function convertDate(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Ymd');
        }
        return self::convertString($value, __METHOD__);
    }

    /**
     * @param TSpaydValue $value
     *
     * @throws Exception\CouldNotConvertValue
     */
    public static function convertInt(mixed $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }
        return self::convertString($value, __METHOD__);
    }

    /**
     * @param TSpaydValue $value
     *
     * @throws Exception\CouldNotConvertValue
     */
    public static function convertMoneyAmount(mixed $value): string
    {
        if ($value instanceof Money) {
            return (new DecimalMoneyFormatter(new ISOCurrencies()))->format($value);
        }
        return self::convertString($value, __METHOD__);
    }

    /**
     * @param TSpaydValue $value
     *
     * @throws Exception\CouldNotConvertValue
     */
    public static function convertMoneyCurrency(mixed $value): string
    {
        if ($value instanceof Money) {
            $value = $value->getCurrency();
        }
        if ($value instanceof Currency) {
            return $value->getCode();
        }
        return self::convertString($value, __METHOD__);
    }

    /**
     * @param TSpaydValue $value
     *
     * @throws Exception\CouldNotConvertValue
     */
    public static function convertString(mixed $value, /** @internal  */ string|null $method = null): string
    {
        $method ??= __METHOD__;
        if (is_string($value)) {
            return $value;
        }
        throw new Exception\CouldNotConvertValue($method, $value);
    }
}
