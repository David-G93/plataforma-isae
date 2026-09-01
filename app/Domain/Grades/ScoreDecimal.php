<?php

declare(strict_types=1);

namespace App\Domain\Grades;

use InvalidArgumentException;

final class ScoreDecimal
{
    private function __construct(
        private readonly string $whole,
        private readonly string $fraction,
    ) {}

    public static function from(int|string $value): self
    {
        $value = is_int($value) ? (string) $value : trim($value);

        if (preg_match('/^(0|[1-9]\d*)(?:\.(\d+))?$/D', $value, $matches) !== 1) {
            throw new InvalidArgumentException('A score must be a positive decimal value.');
        }

        $whole = $matches[1];
        $fraction = rtrim($matches[2] ?? '', '0');

        if ((int) $whole < 1 || (int) $whole > 10 || ((int) $whole === 10 && $fraction !== '')) {
            throw new InvalidArgumentException('A score must be between 1.00 and 10.00.');
        }

        return new self($whole, $fraction);
    }

    public function scale(): int
    {
        return strlen($this->fraction);
    }

    public function scaledTo(int $scale): string
    {
        return self::trimLeadingZeroes($this->whole.str_pad($this->fraction, $scale, '0'));
    }

    public function compare(self $other): int
    {
        $scale = max($this->scale(), $other->scale());

        return self::compareIntegers($this->scaledTo($scale), $other->scaledTo($scale));
    }

    public static function add(string $left, string $right): string
    {
        $carry = 0;
        $result = '';
        $left = strrev($left);
        $right = strrev($right);
        $length = max(strlen($left), strlen($right));

        for ($index = 0; $index < $length; $index++) {
            $sum = ((int) ($left[$index] ?? 0)) + ((int) ($right[$index] ?? 0)) + $carry;
            $result .= (string) ($sum % 10);
            $carry = intdiv($sum, 10);
        }

        if ($carry > 0) {
            $result .= (string) $carry;
        }

        return self::trimLeadingZeroes(strrev($result));
    }

    public static function multiplyBySmallInteger(string $value, int $multiplier): string
    {
        $carry = 0;
        $result = '';

        foreach (str_split(strrev($value)) as $digit) {
            $product = ((int) $digit * $multiplier) + $carry;
            $result .= (string) ($product % 10);
            $carry = intdiv($product, 10);
        }

        while ($carry > 0) {
            $result .= (string) ($carry % 10);
            $carry = intdiv($carry, 10);
        }

        return self::trimLeadingZeroes(strrev($result));
    }

    public static function divideBySmallInteger(string $value, int $divisor): string
    {
        $remainder = 0;
        $result = '';

        foreach (str_split($value) as $digit) {
            $dividend = ($remainder * 10) + (int) $digit;
            $result .= (string) intdiv($dividend, $divisor);
            $remainder = $dividend % $divisor;
        }

        return self::trimLeadingZeroes($result);
    }

    public static function compareIntegers(string $left, string $right): int
    {
        $left = self::trimLeadingZeroes($left);
        $right = self::trimLeadingZeroes($right);

        return strlen($left) <=> strlen($right) ?: $left <=> $right;
    }

    private static function trimLeadingZeroes(string $value): string
    {
        return ltrim($value, '0') ?: '0';
    }
}
