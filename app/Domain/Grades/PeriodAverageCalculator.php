<?php

declare(strict_types=1);

namespace App\Domain\Grades;

use InvalidArgumentException;

final class PeriodAverageCalculator
{
    /**
     * @param  array<int, int|string>  $scores
     */
    public function calculate(array $scores): string
    {
        if ($scores === []) {
            throw new InvalidArgumentException('At least one score is required to calculate a period average.');
        }

        $values = array_map(ScoreDecimal::from(...), $scores);
        $scale = max(array_map(fn (ScoreDecimal $score): int => $score->scale(), $values));
        $sum = '0';

        foreach ($values as $score) {
            $sum = ScoreDecimal::add($sum, $score->scaledTo($scale));
        }

        $quarters = ScoreDecimal::multiplyBySmallInteger($sum, 4);
        $count = count($values);

        for ($quarter = 4; $quarter <= 40; $quarter++) {
            $limit = (string) ($quarter * $count).str_repeat('0', $scale);

            if (ScoreDecimal::compareIntegers($quarters, $limit) <= 0) {
                return sprintf('%d.%02d', intdiv($quarter, 4), ($quarter % 4) * 25);
            }
        }

        throw new InvalidArgumentException('The period average is outside the permitted score range.');
    }
}
