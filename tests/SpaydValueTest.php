<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use PHPUnit\Framework\TestCase;
use Stringable;

class SpaydValueTest extends TestCase
{
    /**
     * @dataProvider dataNormalizesByKey
     * @depends testNormalizes
     */
    public function testNormalizesByKey(SpaydKey|null $key, mixed $value, string $expected): void
    {
        self::assertSame(
            $expected,
            SpaydValue::normalize($key, $value),
        );
    }

    public static function dataNormalizesByKey(): iterable
    {
        foreach (
            [
                [null, 'test', 'test'],
                [SpaydKey::DueDate, new \DateTime('2024-06-04'), '20240604']
            ] as $data
        ) {
            yield $data[0]?->value ?? 'null' => $data;
        }
    }

    /**
     * @dataProvider dataNormalizes
     */
    public function testNormalizes(string $what, mixed $value, string $expected, bool $shouldThrow)
    {
        if ($shouldThrow) {
            self::expectException(Exception\CouldNotNormalizeValue::class);
        }

        self::assertSame(
            $expected,
            call_user_func(SpaydValue::class . '::normalize' . ucfirst($what), $value),
        );
    }

    /**
     * @dataProvider dataNormalizes
     */
    public static function dataNormalizes(): iterable
    {
        $stringable = new class () implements Stringable {
            public function __toString()
            {
                return 'stringable';
            }
        };
        $dateTime = new \DateTime('2024-06-04');
        foreach (
            [
                ['string', 'string', 'string', false],
                ['string', $stringable, 'stringable', false],
                ['string', null, 'null', true],
                ['date', $dateTime, '20240604', false],
                ['date', '20240604', '20240604', false],
                ['date', '2024-06-04', '2024-06-04', true],
            ] as $index => $data
        ) {
            yield sprintf(
                '%s($i%d) = %s',
                $data[0],
                $index,
                $data[3] ? Exception\CouldNotNormalizeValue::class : $data[2],
            ) => $data;
        }
    }
}
