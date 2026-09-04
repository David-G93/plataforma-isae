<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Division;
use App\Models\Grade;
use Illuminate\Database\Seeder;
use RuntimeException;

class AcademicYear2026Seeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::query()
            ->where('year', 2026)
            ->first();

        if (! $academicYear) {
            throw new RuntimeException(
                'No existe el ciclo lectivo 2026. Ejecutá primero AcademicStructureSeeder.',
            );
        }

        $structure = [
            'primario-1' => '1° Grado',
            'primario-2' => '2° Grado',
            'primario-3' => '3° Grado',
            'primario-4' => '4° Grado',
            'primario-5' => '5° Grado',
            'primario-6' => '6° Grado',
            'primario-7' => '7° Grado',

            'secundario-1' => '1° Año',
            'secundario-2' => '2° Año',
            'secundario-3' => '3° Año',
            'secundario-4' => '4° Año',
            'secundario-5' => '5° Año',
        ];

        foreach ($structure as $gradeCode => $courseName) {
            $grade = Grade::query()
                ->where('code', $gradeCode)
                ->first();

            if (! $grade) {
                throw new RuntimeException(
                    "No se encontró el grado/año con código {$gradeCode}.",
                );
            }

            $course = Course::query()->updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'grade_id' => $grade->id,
                ],
                [
                    'name' => $courseName,
                    'is_active' => true,
                ],
            );

            Division::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'name' => 'Única',
                ],
                [
                    'shift' => 'Mañana',
                    'is_active' => true,
                ],
            );
        }
    }
}