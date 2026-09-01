<?php

declare(strict_types=1);

namespace App\Domain\Grades;

final class PerformanceLevelResolver
{
    public function resolve(int|string $score): string
    {
        $score = ScoreDecimal::from($score);

        if ($score->compare(ScoreDecimal::from('6')) < 0) {
            return 'LI';
        }

        if ($score->compare(ScoreDecimal::from('7')) < 0) {
            return 'LB';
        }

        if ($score->compare(ScoreDecimal::from('9')) < 0) {
            return 'LS';
        }

        return 'LD';
    }
}
