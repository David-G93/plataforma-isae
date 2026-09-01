<?php

use App\Domain\Grades\CanCalculateFinalGrade;
use App\Domain\Grades\FinalGradeCalculator;
use App\Domain\Grades\PerformanceLevelResolver;
use App\Domain\Grades\PeriodAverageCalculator;
use App\Domain\Grades\ValidateScore;

it('validates partial scores without restricting quarter increments', function (int|string $score, bool $valid): void {
    expect((new ValidateScore)->isValid($score))->toBe($valid);
})->with([
    'zero is invalid' => [0, false],
    'one is valid' => [1, true],
    'ten is valid' => [10, true],
    'above ten is invalid' => ['10.01', false],
    'six point sixty is valid' => ['6.60', true],
    'eight point thirty-three is valid' => ['8.33', true],
    'negative values are invalid' => ['-1', false],
    'empty values are invalid' => ['', false],
    'letters are invalid' => ['eight', false],
]);

it('rounds period averages upward to the next quarter', function (string $score, string $expected): void {
    expect((new PeriodAverageCalculator)->calculate([$score]))->toBe($expected);
})->with([
    '6.51 becomes 6.75' => ['6.51', '6.75'],
    '6.60 becomes 6.75' => ['6.60', '6.75'],
    '6.75 stays 6.75' => ['6.75', '6.75'],
    '6.76 becomes 7.00' => ['6.76', '7.00'],
]);

it('uses all valid partial scores for a period average', function (): void {
    expect((new PeriodAverageCalculator)->calculate(['6.00', '6.26']))->toBe('6.25');
});

it('rejects empty or invalid period averages', function (array $scores): void {
    (new PeriodAverageCalculator)->calculate($scores);
})->with([
    'no scores' => [[]],
    'invalid score' => [['0']],
])->throws(InvalidArgumentException::class);

it('truncates final grades to two decimals', function (array $periodAverages, string $expected): void {
    expect((new FinalGradeCalculator)->calculate($periodAverages))->toBe($expected);
})->with([
    '8.66 final grade' => [['9.50', '8.25', '8.25'], '8.66'],
    '9.08 final grade' => [['8.25', '9.50', '9.50'], '9.08'],
]);

it('only calculates a final grade with exactly three valid periods', function (array $periodAverages, bool $expected): void {
    expect((new CanCalculateFinalGrade)($periodAverages))->toBe($expected);
})->with([
    'three periods' => [['6.00', '7.00', '8.00'], true],
    'missing period' => [['6.00', '7.00'], false],
    'extra period' => [['6.00', '7.00', '8.00', '9.00'], false],
    'invalid period' => [['6.00', '0', '8.00'], false],
]);

it('rejects incomplete final grade inputs', function (): void {
    (new FinalGradeCalculator)->calculate(['6.00', '7.00']);
})->throws(InvalidArgumentException::class);

it('resolves the current performance level ranges', function (string $score, string $expected): void {
    expect((new PerformanceLevelResolver)->resolve($score))->toBe($expected);
})->with([
    '5.99 is LI' => ['5.99', 'LI'],
    '6 is LB' => ['6', 'LB'],
    '6.99 is LB' => ['6.99', 'LB'],
    '7 is LS' => ['7', 'LS'],
    '8.99 is LS' => ['8.99', 'LS'],
    '9 is LD' => ['9', 'LD'],
    '10 is LD' => ['10', 'LD'],
]);
