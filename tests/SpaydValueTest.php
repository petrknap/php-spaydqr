<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use DateTime;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Stringable;

class SpaydValueTest extends TestCase
{
    /**
     * @dataProvider dataConvertsByKey
     * @depends testConverts
     */
    public function testConvertsByKey(SpaydKey|null $key, mixed $value, string $expected): void
    {
        self::assertSame(
            $expected,
            SpaydValue::convert($key, $value),
        );
    }

    public static function dataConvertsByKey(): iterable
    {
        foreach (
            [
                [null, 'test', 'test'],
                [SpaydKey::Amount, Money::CZK(123), '1.23'],
                [SpaydKey::DueDate, new DateTime('2024-06-04'), '20240604'],
                [SpaydKey::VariableSymbol, 1, '1'],
            ] as $data
        ) {
            yield $data[0]?->value ?? 'null' => $data;
        }
    }

    /**
     * @dataProvider dataConverts
     */
    public function testConverts(string $what, mixed $value, string $expected, bool $shouldThrow)
    {
        if ($shouldThrow) {
            self::expectException(Exception\CouldNotConvertValue::class);
        }

        self::assertSame(
            $expected,
            call_user_func(SpaydValue::class . '::convert' . ucfirst($what), $value),
        );
    }

    /**
     * @dataProvider dataConverts
     */
    public static function dataConverts(): iterable
    {
        $unsupported = new \stdClass();
        foreach (
            [
                ['date', new DateTime('2024-06-04'), '20240604', false],
                ['date', '20240604', '20240604', false],
                ['date', $unsupported, 'unsupported', true],
                ['int', 1, '1', false],
                ['int', '1', '1', false],
                ['int', $unsupported, 'unsupported', true],
                ['moneyAmount', Money::CZK(123), '1.23', false],
                ['moneyAmount', '1.23', '1.23', false],
                ['moneyAmount', $unsupported, 'unsupported', true],
                ['string', 'string', 'string', false],
                ['string', $unsupported, 'unsupported', true],
            ] as $index => $data
        ) {
            yield sprintf(
                '%s($i%d) = %s',
                $data[0],
                $index,
                $data[3] ? Exception\CouldNotConvertValue::class : $data[2],
            ) => $data;
        }
    }
}
