<?php

declare(strict_types=1);

namespace App\Domain\Grades;

final class CanCalculateFinalGrade
{
    /**
     * @param  array<int, int|string>  $periodAverages
     */
    public function __invoke(array $periodAverages): bool
    {
        if (count($periodAverages) !== 3) {
            return false;
        }

        foreach ($periodAverages as $periodAverage) {
            if (! (new ValidateScore)->isValid($periodAverage)) {
                return false;
            }
        }

        return true;
    }
}
