<?php

declare(strict_types=1);

namespace App\Domain\Grades;

final class ValidateScore
{
    public function __invoke(int|string $score): bool
    {
        return $this->isValid($score);
    }

    public function isValid(int|string $score): bool
    {
        try {
            ScoreDecimal::from($score);

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
