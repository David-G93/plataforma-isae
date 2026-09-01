<?php

declare(strict_types=1);

namespace App\Domain\Grades;

use InvalidArgumentException;

final class FinalGradeCalculator
{
    /**
     * @param  array<int, int|string>  $periodAverages
     */
    public function calculate(array $periodAverages): string
    {
        if (! (new CanCalculateFinalGrade)($periodAverages)) {
            throw new InvalidArgumentException('Exactly three valid period averages are required.');
        }

        $values = [];
        $scale = 0;

        foreach ($periodAverages as $periodAverage) {
            $score = ScoreDecimal::from($periodAverage);
            $values[] = $score;
            $scale = max($scale, $score->scale());
        }

        $sum = '0';

        foreach ($values as $score) {
            $sum = ScoreDecimal::add($sum, $score->scaledTo($scale));
        }

        $cents = ScoreDecimal::divideBySmallInteger(
            ScoreDecimal::multiplyBySmallInteger($sum, 100),
            3,
        );
        $cents = $scale === 0 ? $cents : substr(str_pad($cents, $scale + 1, '0', STR_PAD_LEFT), 0, -$scale);
        $cents = (int) $cents;

        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }
}
