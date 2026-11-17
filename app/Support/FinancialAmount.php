<?php

namespace App\Support;

use InvalidArgumentException;

final class FinancialAmount
{
    public static function cents(string|int|float|null $amount): int
    {
        $value = trim((string) ($amount ?? '0'));
        if (! preg_match('/^-?\d+(?:\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException('Invalid monetary amount.');
        }

        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '-');
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        return $negative ? -$cents : $cents;
    }

    public static function decimal(int $cents): string
    {
        $negative = $cents < 0;
        $absolute = abs($cents);

        return ($negative ? '-' : '').intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function sum(iterable $amounts): string
    {
        $total = 0;
        foreach ($amounts as $amount) {
            $total += self::cents($amount);
        }

        return self::decimal($total);
    }

    public static function percentage(string|int|float $amount, string|int|float $percentage): string
    {
        $cents = self::cents($amount);
        $rate = trim((string) $percentage);
        if (! preg_match('/^\d+(?:\.\d{1,4})?$/', $rate)) {
            throw new InvalidArgumentException('Invalid commission percentage.');
        }

        [$whole, $fraction] = array_pad(explode('.', $rate, 2), 2, '');
        $scaledRate = ((int) $whole * 10000) + (int) str_pad($fraction, 4, '0');
        $divisor = 1000000;
        $wholePart = intdiv($cents, $divisor) * $scaledRate;
        $remainder = $cents % $divisor;
        $fractionPart = intdiv(($remainder * $scaledRate) + intdiv($divisor, 2), $divisor);

        return self::decimal($wholePart + $fractionPart);
    }
}
