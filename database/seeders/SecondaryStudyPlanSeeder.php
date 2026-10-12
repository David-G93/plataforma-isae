<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Modality;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanSubjectModalityRule;
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

            $naturalSciencesModality = Modality::query()
                ->where('code', 'ciencias-naturales')
                ->first();

            $informaticsModality = Modality::query()
                ->where('code', 'informatica')
                ->first();

            if (! $naturalSciencesModality) {
                throw new RuntimeException(
                    'No existe la modalidad Ciencias Naturales.',
                );
            }

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
            | Limpiar plan provisional viejo
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
            | Catálogo
            |--------------------------------------------------------------------------
            */

            $subjectCatalog = [
                'biologia' =>
                    'Biología',

                'educacion-tecnologica' =>
                    'Educ. Tecnológica',

                'educacion-fisica' =>
                    'Educación Física',

                'fisica-quimica' =>
                    'Física y Química',

                'formacion-etica-ciudadana' =>
                    'Formación Ética y Ciudadana',

                'geografia' =>
                    'Geografía',

                'historia' =>
                    'Historia',

                'ingles' =>
                    'Inglés',

                'lengua-y-literatura' =>
                    'Lengua y Literatura',

                'matematica' =>
                    'Matemática',

                'plastica' =>
                    'Plástica',

                'teatro' =>
                    'Teatro',

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

                'construccion-ciudadania' =>
                    'Construcción de Ciudadanía',

                'educacion-artistica' =>
                    'Educación Artística',

                'fisica' =>
                    'Física',

                'geologia' =>
                    'Geología',

                'quimica' =>
                    'Química',

                'sistemas-digitales-informacion' =>
                    'Sist. Digitales de la Información',

                'tics-aplicaciones' =>
                    'TICS y Aplicaciones',

                'ciencia-tecnologia-sociedad' =>
                    'Ciencia, Tecnología y Sociedad',

                'comunicacion-audiovisual' =>
                    'Comunicación Audiovisual',

                'eo-microemprendimiento' =>
                    'E.O.: Microemprendimiento',

                'ecologia' =>
                    'Ecología',

                'economia' =>
                    'Economía',

                'lenguajes-tecnicas-programacion' =>
                    'Lenguajes y Téc. de Programación',

                /*
                |--------------------------------------------------------------------------
                | 5° Año
                |--------------------------------------------------------------------------
                */

                'astronomia' =>
                    'Astronomía',

                'biofisica' =>
                    'Biofísica',

                'bioquimica' =>
                    'Bioquímica',

                'eo-produccion-audiovisual-n' =>
                    'E.O.: Producción Audiovisual (N)',

                'filosofia' =>
                    'Filosofía',

                'produccion-audiovisual-digital-i' =>
                    'Producción Audiovisual Digital (I)',

                'proyecto-investigacion-intervencion' =>
                    'Proyecto de Inv. e Intervención',

                'robotica' =>
                    'Robótica',
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
            | 1° Año - Bioinformática
            |--------------------------------------------------------------------------
            */

            $firstYear = $this->grade('secundario-1');

            $firstYearSubjects = [
                ['code' => 'biologia', 'modality_id' => null],
                ['code' => 'educacion-tecnologica', 'modality_id' => null],
                ['code' => 'educacion-fisica', 'modality_id' => null],
                ['code' => 'fisica-quimica', 'modality_id' => null],
                ['code' => 'formacion-etica-ciudadana', 'modality_id' => null],
                ['code' => 'geografia', 'modality_id' => null],
                ['code' => 'historia', 'modality_id' => null],
                ['code' => 'ingles', 'modality_id' => null],
                ['code' => 'laboratorio-electronica-i', 'modality_id' => null],
                ['code' => 'lengua-y-literatura', 'modality_id' => null],
                ['code' => 'matematica', 'modality_id' => null],
                ['code' => 'plastica', 'modality_id' => null],
                ['code' => 'programacion-i', 'modality_id' => null],
                ['code' => 'taller-maker-i', 'modality_id' => null],
                ['code' => 'teatro', 'modality_id' => null],
            ];

            $this->syncGradeSubjects(
                $bioinformaticsPlan,
                $firstYear,
                $firstYearSubjects,
                $subjects,
            );

            /*
            |--------------------------------------------------------------------------
            | 2° Año - Plan legado
            |--------------------------------------------------------------------------
            */

            $secondYear = $this->grade('secundario-2');

            $secondYearSubjects = [
                ['code' => 'biologia', 'modality_id' => null],

                [
                    'code' => 'ei-laboratorio-electronica',
                    'modality_id' => $informaticsModality->id,
                ],

                ['code' => 'educacion-tecnologica', 'modality_id' => null],
                ['code' => 'educacion-fisica', 'modality_id' => null],
                ['code' => 'fisica-quimica', 'modality_id' => null],
                ['code' => 'formacion-etica-ciudadana', 'modality_id' => null],
                ['code' => 'geografia', 'modality_id' => null],
                ['code' => 'historia', 'modality_id' => null],

                /*
                 * Informática es académicamente común:
                 * mismo contenido, notas, asistencia, boletín y futura aula.
                 */
                ['code' => 'informatica', 'modality_id' => null],

                ['code' => 'ingles', 'modality_id' => null],
                ['code' => 'lengua-y-literatura', 'modality_id' => null],
                ['code' => 'matematica', 'modality_id' => null],
                ['code' => 'plastica', 'modality_id' => null],
                ['code' => 'teatro', 'modality_id' => null],
            ];

            $this->syncGradeSubjects(
                $legacyPlan,
                $secondYear,
                $secondYearSubjects,
                $subjects,
            );

            /*
            |--------------------------------------------------------------------------
            | 3° Año - Plan legado
            |--------------------------------------------------------------------------
            */

            $thirdYear = $this->grade('secundario-3');

            $thirdYearSubjects = [
                ['code' => 'biologia', 'modality_id' => null],
                ['code' => 'construccion-ciudadania', 'modality_id' => null],
                ['code' => 'educacion-artistica', 'modality_id' => null],
                ['code' => 'educacion-fisica', 'modality_id' => null],
                ['code' => 'fisica', 'modality_id' => null],
                ['code' => 'geografia', 'modality_id' => null],

                [
                    'code' => 'geologia',
                    'modality_id' => $naturalSciencesModality->id,
                ],

                ['code' => 'historia', 'modality_id' => null],
                ['code' => 'ingles', 'modality_id' => null],
                ['code' => 'lengua-y-literatura', 'modality_id' => null],
                ['code' => 'matematica', 'modality_id' => null],

                /*
                 * Química es una sola cursada para todo el curso.
                 */
                ['code' => 'quimica', 'modality_id' => null],

                [
                    'code' => 'sistemas-digitales-informacion',
                    'modality_id' => $informaticsModality->id,
                ],

                [
                    'code' => 'tics-aplicaciones',
                    'modality_id' => $informaticsModality->id,
                ],
            ];

            $thirdYearPlanSubjects = $this->syncGradeSubjects(
                $legacyPlan,
                $thirdYear,
                $thirdYearSubjects,
                $subjects,
            );

            /*
            |--------------------------------------------------------------------------
            | Regla especial: Química de 3° Año
            |--------------------------------------------------------------------------
            */

            $chemistryPlanSubject =
                $thirdYearPlanSubjects['quimica'];

            StudyPlanSubjectModalityRule::query()->updateOrCreate(
                [
                    'study_plan_subject_id' =>
                        $chemistryPlanSubject->id,

                    'modality_id' =>
                        $naturalSciencesModality->id,
                ],
                [
                    'counts_for_curricular_average' => true,
                    'counts_for_institutional_average' => true,
                ],
            );

            StudyPlanSubjectModalityRule::query()->updateOrCreate(
                [
                    'study_plan_subject_id' =>
                        $chemistryPlanSubject->id,

                    'modality_id' =>
                        $informaticsModality->id,
                ],
                [
                    'counts_for_curricular_average' => false,
                    'counts_for_institutional_average' => true,
                ],
            );

            /*
            |--------------------------------------------------------------------------
            | 4° Año - Plan legado
            |--------------------------------------------------------------------------
            |
            | 20 alumnos = común
            | 11 alumnos = Cs. Naturales
            |  9 alumnos = Informática
            |--------------------------------------------------------------------------
            */

            $fourthYear = $this->grade('secundario-4');

            $fourthYearSubjects = [
                [
                    'code' => 'biologia',
                    'modality_id' => $naturalSciencesModality->id,
                ],

                [
                    'code' => 'ciencia-tecnologia-sociedad',
                    'modality_id' => null,
                ],

                [
                    'code' => 'comunicacion-audiovisual',
                    'modality_id' => $informaticsModality->id,
                ],

                [
                    'code' => 'construccion-ciudadania',
                    'modality_id' => null,
                ],

                [
                    'code' => 'eo-microemprendimiento',
                    'modality_id' => null,
                ],

                [
                    'code' => 'ecologia',
                    'modality_id' => $naturalSciencesModality->id,
                ],

                [
                    'code' => 'economia',
                    'modality_id' => null,
                ],

                [
                    'code' => 'educacion-fisica',
                    'modality_id' => null,
                ],

                [
                    'code' => 'fisica',
                    'modality_id' => $naturalSciencesModality->id,
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
                    'code' => 'lenguajes-tecnicas-programacion',
                    'modality_id' => $informaticsModality->id,
                ],

                [
                    'code' => 'matematica',
                    'modality_id' => null,
                ],

                [
                    'code' => 'quimica',
                    'modality_id' => null,
                ],

                [
                    'code' => 'sistemas-digitales-informacion',
                    'modality_id' => $informaticsModality->id,
                ],
            ];

            $this->syncGradeSubjects(
                $legacyPlan,
                $fourthYear,
                $fourthYearSubjects,
                $subjects,
            );

            /*
            |--------------------------------------------------------------------------
            | 5° Año - Plan legado
            |--------------------------------------------------------------------------
            |
            | 32 alumnos = común
            |  5 alumnos = Ciencias Naturales
            | 27 alumnos = Informática
            |
            | Todas las materias correspondientes al alumno cuentan
            | curricular + institucional.
            |--------------------------------------------------------------------------
            */

            $fifthYear = $this->grade('secundario-5');

            $fifthYearSubjects = [
                /*
                 * Ciencias Naturales
                 */
                [
                    'code' => 'astronomia',
                    'modality_id' => $naturalSciencesModality->id,
                ],

                /*
                 * Comunes
                 */
                [
                    'code' => 'biofisica',
                    'modality_id' => null,
                ],

                [
                    'code' => 'bioquimica',
                    'modality_id' => null,
                ],

                [
                    'code' => 'ciencia-tecnologia-sociedad',
                    'modality_id' => null,
                ],

                [
                    'code' => 'construccion-ciudadania',
                    'modality_id' => null,
                ],

                /*
                 * Ciencias Naturales
                 */
                [
                    'code' => 'eo-produccion-audiovisual-n',
                    'modality_id' => $naturalSciencesModality->id,
                ],

                /*
                 * Comunes
                 */
                [
                    'code' => 'educacion-fisica',
                    'modality_id' => null,
                ],

                [
                    'code' => 'filosofia',
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

                /*
                 * Informática
                 */
                [
                    'code' => 'lenguajes-tecnicas-programacion',
                    'modality_id' => $informaticsModality->id,
                ],

                /*
                 * Común
                 */
                [
                    'code' => 'matematica',
                    'modality_id' => null,
                ],

                /*
                 * Informática
                 */
                [
                    'code' => 'produccion-audiovisual-digital-i',
                    'modality_id' => $informaticsModality->id,
                ],

                /*
                 * Común
                 */
                [
                    'code' => 'proyecto-investigacion-intervencion',
                    'modality_id' => null,
                ],

                /*
                 * Informática
                 */
                [
                    'code' => 'robotica',
                    'modality_id' => $informaticsModality->id,
                ],
            ];

            $this->syncGradeSubjects(
                $legacyPlan,
                $fifthYear,
                $fifthYearSubjects,
                $subjects,
            );

            /*
            |--------------------------------------------------------------------------
            | Cursos 2026
            |--------------------------------------------------------------------------
            |
            | 1° → nueva cohorte Bioinformática
            | 2° a 5° → plan legado
            |--------------------------------------------------------------------------
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

    private function syncGradeSubjects(
        StudyPlan $studyPlan,
        Grade $grade,
        array $subjectData,
        array $subjects,
    ): array {
        StudyPlanSubject::query()
            ->where('study_plan_id', $studyPlan->id)
            ->where('grade_id', $grade->id)
            ->delete();

        $created = [];

        foreach ($subjectData as $index => $data) {
            $subject = $subjects[$data['code']] ?? null;

            if (! $subject) {
                throw new RuntimeException(
                    "No existe la materia {$data['code']}.",
                );
            }

            $planSubject = StudyPlanSubject::query()->create([
                'study_plan_id' => $studyPlan->id,
                'grade_id' => $grade->id,
                'subject_id' => $subject->id,
                'modality_id' => $data['modality_id'],
                'order' => $index + 1,
                'is_active' => true,
            ]);

            $created[$data['code']] = $planSubject;
        }

        return $created;
    }
}