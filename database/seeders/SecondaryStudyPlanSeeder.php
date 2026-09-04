<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Modality;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SecondaryStudyPlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $level = Level::query()
                ->where('code', 'secundario')
                ->first();

            if (! $level) {
                throw new RuntimeException(
                    'No existe el Nivel Secundario.',
                );
            }

            $academicYear = AcademicYear::query()
                ->where('year', 2026)
                ->first();

            if (! $academicYear) {
                throw new RuntimeException(
                    'No existe el ciclo lectivo 2026.',
                );
            }

            $informaticsModality = Modality::query()
                ->where('code', 'informatica')
                ->first();

            if (! $informaticsModality) {
                throw new RuntimeException(
                    'No existe la modalidad Informática.',
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Planes secundarios
            |--------------------------------------------------------------------------
            */

            $bioinformaticsPlan = StudyPlan::query()->updateOrCreate(
                [
                    'code' => 'secundario-bioinformatica',
                ],
                [
                    'level_id' => $level->id,
                    'name' => 'Secundario - Bioinformática',
                    'effective_from_year' => 2026,
                    'effective_to_year' => null,
                    'is_active' => true,
                ],
            );

            $legacyPlan = StudyPlan::query()->updateOrCreate(
                [
                    'code' => 'secundario-legado',
                ],
                [
                    'level_id' => $level->id,
                    'name' => 'Secundario - Cs. Naturales / Informática',
                    'effective_from_year' => null,
                    'effective_to_year' => 2029,
                    'is_active' => true,
                ],
            );

            /*
            |--------------------------------------------------------------------------
            | Eliminar plan provisional viejo
            |--------------------------------------------------------------------------
            */

            $oldStudyPlan = StudyPlan::query()
                ->where('code', 'plan-secundario')
                ->first();

            if ($oldStudyPlan) {
                Course::query()
                    ->where('study_plan_id', $oldStudyPlan->id)
                    ->update([
                        'study_plan_id' => null,
                    ]);

                StudyPlanSubject::query()
                    ->where('study_plan_id', $oldStudyPlan->id)
                    ->delete();

                $oldStudyPlan->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | Catálogo de materias necesarias
            |--------------------------------------------------------------------------
            |
            | Subject es global.
            |
            | Si una materia ya existe por Primaria, como Matemática,
            | Inglés, Geografía, Historia, etc., se reutiliza.
            |
            */

            $subjectCatalog = [
                'biologia' => 'Biología',
                'educacion-tecnologica' => 'Educ. Tecnológica',
                'educacion-fisica' => 'Educación Física',
                'fisica-quimica' => 'Física y Química',
                'formacion-etica-ciudadana' => 'Formación Ética y Ciudadana',
                'geografia' => 'Geografía',
                'historia' => 'Historia',
                'ingles' => 'Inglés',
                'lengua-y-literatura' => 'Lengua y Literatura',
                'matematica' => 'Matemática',
                'plastica' => 'Plástica',
                'teatro' => 'Teatro',

                'laboratorio-electronica-i' =>
                    'Lab. de Electrónica I',

                'programacion-i' =>
                    'Programación I',

                'taller-maker-i' =>
                    'Taller Maker I',

                'ei-laboratorio-electronica' =>
                    'E.I. Lab. de Electrónica',

                'informatica' =>
                    'Informática',
            ];

            $subjects = [];

            foreach ($subjectCatalog as $code => $name) {
                $subjects[$code] = Subject::query()->updateOrCreate(
                    [
                        'code' => $code,
                    ],
                    [
                        'name' => $name,
                        'description' => null,
                        'is_active' => true,
                    ],
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 1° Año 2026 - Bioinformática
            |--------------------------------------------------------------------------
            |
            | Toda la cohorte cursa las mismas materias.
            |
            | Por eso todas tienen modality_id = null.
            |
            */

            $firstYear = $this->grade('secundario-1');

            $firstYearSubjects = [
                'biologia',
                'educacion-tecnologica',
                'educacion-fisica',
                'fisica-quimica',
                'formacion-etica-ciudadana',
                'geografia',
                'historia',
                'ingles',
                'laboratorio-electronica-i',
                'lengua-y-literatura',
                'matematica',
                'plastica',
                'programacion-i',
                'taller-maker-i',
                'teatro',
            ];

            /*
             * Sincronizamos exactamente la estructura confirmada.
             */
            StudyPlanSubject::query()
                ->where('study_plan_id', $bioinformaticsPlan->id)
                ->where('grade_id', $firstYear->id)
                ->delete();

            foreach ($firstYearSubjects as $index => $subjectCode) {
                StudyPlanSubject::query()->create([
                    'study_plan_id' => $bioinformaticsPlan->id,
                    'grade_id' => $firstYear->id,
                    'subject_id' => $subjects[$subjectCode]->id,
                    'modality_id' => null,
                    'order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 2° Año 2026 - Plan legado
            |--------------------------------------------------------------------------
            |
            | Regla institucional confirmada:
            |
            | - La mayoría de las materias son comunes.
            |
            | - Informática aparece tanto para alumnos de Ciencias Naturales
            |   como de Informática. Aunque operativamente pueda haber un
            |   desdoble, académicamente funciona como UNA materia:
            |
            |     * mismos contenidos
            |     * mismo boletín
            |     * calificaciones juntas
            |     * asistencia junta
            |     * futura aula virtual compartida
            |
            |   Por eso Informática lleva modality_id = null.
            |
            | - E.I. Lab. de Electrónica es exclusiva de la modalidad
            |   Informática.
            |
            */

            $secondYear = $this->grade('secundario-2');

            $secondYearSubjects = [
                [
                    'code' => 'biologia',
                    'modality_id' => null,
                ],
                [
                    'code' => 'ei-laboratorio-electronica',
                    'modality_id' => $informaticsModality->id,
                ],
                [
                    'code' => 'educacion-tecnologica',
                    'modality_id' => null,
                ],
                [
                    'code' => 'educacion-fisica',
                    'modality_id' => null,
                ],
                [
                    'code' => 'fisica-quimica',
                    'modality_id' => null,
                ],
                [
                    'code' => 'formacion-etica-ciudadana',
                    'modality_id' => null,
                ],
                [
                    'code' => 'geografia',
                    'modality_id' => null,
                ],
                [
                    'code' => 'historia',
                    'modality_id' => null,
                ],
                [
                    'code' => 'informatica',
                    'modality_id' => null,
                ],
                [
                    'code' => 'ingles',
                    'modality_id' => null,
                ],
                [
                    'code' => 'lengua-y-literatura',
                    'modality_id' => null,
                ],
                [
                    'code' => 'matematica',
                    'modality_id' => null,
                ],
                [
                    'code' => 'plastica',
                    'modality_id' => null,
                ],
                [
                    'code' => 'teatro',
                    'modality_id' => null,
                ],
            ];

            /*
             * Eliminamos únicamente las asociaciones anteriores de 2° Año
             * para dejar exactamente la estructura real confirmada.
             */
            StudyPlanSubject::query()
                ->where('study_plan_id', $legacyPlan->id)
                ->where('grade_id', $secondYear->id)
                ->delete();

            foreach ($secondYearSubjects as $index => $subjectData) {
                $subject = $subjects[$subjectData['code']] ?? null;

                if (! $subject) {
                    throw new RuntimeException(
                        "No existe la materia {$subjectData['code']}.",
                    );
                }

                StudyPlanSubject::query()->create([
                    'study_plan_id' => $legacyPlan->id,
                    'grade_id' => $secondYear->id,
                    'subject_id' => $subject->id,
                    'modality_id' => $subjectData['modality_id'],
                    'order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Cursos 2026 y plan correspondiente
            |--------------------------------------------------------------------------
            |
            | La nueva modalidad avanza por cohorte.
            |
            | 2026:
            | 1°       → Bioinformática
            | 2° a 5° → Plan legado
            |
            */

            $courseAssignments = [
                'secundario-1' => $bioinformaticsPlan->id,
                'secundario-2' => $legacyPlan->id,
                'secundario-3' => $legacyPlan->id,
                'secundario-4' => $legacyPlan->id,
                'secundario-5' => $legacyPlan->id,
            ];

            foreach ($courseAssignments as $gradeCode => $studyPlanId) {
                $grade = $this->grade($gradeCode);

                $course = Course::query()
                    ->where('academic_year_id', $academicYear->id)
                    ->where('grade_id', $grade->id)
                    ->first();

                if (! $course) {
                    throw new RuntimeException(
                        "No existe el curso 2026 para {$grade->name}.",
                    );
                }

                $course->update([
                    'study_plan_id' => $studyPlanId,
                ]);
            }
        });
    }

    private function grade(string $code): Grade
    {
        $grade = Grade::query()
            ->where('code', $code)
            ->first();

        if (! $grade) {
            throw new RuntimeException(
                "No existe el grado/año con código {$code}.",
            );
        }

        return $grade;
    }
}