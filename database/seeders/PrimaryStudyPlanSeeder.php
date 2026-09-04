<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use RuntimeException;

class PrimaryStudyPlanSeeder extends Seeder
{
    public function run(): void
    {
        $studyPlan = StudyPlan::query()
            ->where('code', 'plan-primario')
            ->first();

        if (! $studyPlan) {
            throw new RuntimeException(
                'No existe el Plan Primario con código plan-primario.',
            );
        }

        $subjectCatalog = [
            'cc-educacion-para-la-vida' => [
                'name' => 'C.C. y Educación para la Vida',
            ],

            'ciencias-naturales' => [
                'name' => 'Ciencias Naturales',
            ],

            'ciencias-sociales' => [
                'name' => 'Ciencias Sociales',
            ],

            'danza' => [
                'name' => 'Danza',
            ],

            'educacion-fisica' => [
                'name' => 'Educación Física',
            ],

            'educacion-tecnologica' => [
                'name' => 'Educación Tecnológica',
            ],

            'informatica' => [
                'name' => 'Informática',
            ],

            'ingles' => [
                'name' => 'Inglés',
            ],

            'lengua-y-literatura' => [
                'name' => 'Lengua y Literatura',
            ],

            'matematica' => [
                'name' => 'Matemática',
            ],

            'musica' => [
                'name' => 'Música',
            ],

            'plastica-visual' => [
                'name' => 'Plástica Visual',
            ],

            'robotica' => [
                'name' => 'Robótica',
            ],

            'taller-de-lectura' => [
                'name' => 'Taller de lectura',
            ],

            'teatro' => [
                'name' => 'Teatro',
            ],

            'taller-de-tecnologia' => [
                'name' => 'Taller de Tecnología',
            ],

            'educacion-artistica-plastica-visual' => [
                'name' => 'Ed. Artística: Plástica Visual',
            ],

            'educacion-artistica-teatro' => [
                'name' => 'Ed. Artística: Teatro',
            ],

            'geografia' => [
                'name' => 'Geografía',
            ],

            'historia' => [
                'name' => 'Historia',
            ],

            'laboratorio-desarrollo-tecnologico' => [
                'name' => 'Lab. de Desarrollo Tecnológico',
            ],
        ];

        $gradeSubjects = [
            'primario-1' => [
                'cc-educacion-para-la-vida',
                'ciencias-naturales',
                'ciencias-sociales',
                'danza',
                'educacion-fisica',
                'educacion-tecnologica',
                'informatica',
                'ingles',
                'lengua-y-literatura',
                'matematica',
                'musica',
                'plastica-visual',
                'robotica',
                'taller-de-lectura',
                'teatro',
            ],

            'primario-2' => [
                'cc-educacion-para-la-vida',
                'ciencias-naturales',
                'ciencias-sociales',
                'danza',
                'educacion-fisica',
                'educacion-tecnologica',
                'informatica',
                'ingles',
                'lengua-y-literatura',
                'matematica',
                'musica',
                'plastica-visual',
                'robotica',
                'taller-de-lectura',
                'teatro',
            ],

            'primario-3' => [
                'cc-educacion-para-la-vida',
                'ciencias-naturales',
                'ciencias-sociales',
                'danza',
                'educacion-fisica',
                'educacion-tecnologica',
                'informatica',
                'ingles',
                'lengua-y-literatura',
                'matematica',
                'musica',
                'plastica-visual',
                'robotica',
                'taller-de-lectura',
                'teatro',
            ],

            'primario-4' => [
                'cc-educacion-para-la-vida',
                'ciencias-naturales',
                'ciencias-sociales',
                'danza',
                'educacion-fisica',
                'educacion-tecnologica',
                'informatica',
                'ingles',
                'lengua-y-literatura',
                'matematica',
                'musica',
                'plastica-visual',
                'robotica',
                'taller-de-lectura',
                'taller-de-tecnologia',
                'teatro',
            ],

            'primario-5' => [
                'cc-educacion-para-la-vida',
                'ciencias-naturales',
                'ciencias-sociales',
                'danza',
                'educacion-fisica',
                'educacion-tecnologica',
                'informatica',
                'ingles',
                'lengua-y-literatura',
                'matematica',
                'musica',
                'plastica-visual',
                'robotica',
                'taller-de-lectura',
                'taller-de-tecnologia',
                'teatro',
            ],

            'primario-6' => [
                'cc-educacion-para-la-vida',
                'ciencias-naturales',
                'ciencias-sociales',
                'danza',
                'educacion-fisica',
                'educacion-tecnologica',
                'informatica',
                'ingles',
                'lengua-y-literatura',
                'matematica',
                'musica',
                'plastica-visual',
                'robotica',
                'taller-de-lectura',
                'taller-de-tecnologia',
                'teatro',
            ],

            'primario-7' => [
                'cc-educacion-para-la-vida',
                'ciencias-naturales',
                'educacion-artistica-plastica-visual',
                'educacion-artistica-teatro',
                'educacion-fisica',
                'educacion-tecnologica',
                'geografia',
                'historia',
                'informatica',
                'ingles',
                'laboratorio-desarrollo-tecnologico',
                'lengua-y-literatura',
                'matematica',
                'robotica',
                'taller-de-lectura',
            ],
        ];

        $subjects = [];

        foreach ($subjectCatalog as $code => $subjectData) {
            $subjects[$code] = Subject::query()->updateOrCreate(
                [
                    'code' => $code,
                ],
                [
                    'name' => $subjectData['name'],
                    'description' => null,
                    'is_active' => true,
                ],
            );
        }

        foreach ($gradeSubjects as $gradeCode => $subjectCodes) {
            $grade = Grade::query()
                ->where('code', $gradeCode)
                ->first();

            if (! $grade) {
                throw new RuntimeException(
                    "No existe el grado con código {$gradeCode}.",
                );
            }

            if ($studyPlan->level_id !== $grade->level_id) {
                throw new RuntimeException(
                    "El Plan Primario no corresponde al nivel del grado {$gradeCode}.",
                );
            }

            foreach ($subjectCodes as $index => $subjectCode) {
                $subject = $subjects[$subjectCode] ?? null;

                if (! $subject) {
                    throw new RuntimeException(
                        "No existe la materia con código {$subjectCode}.",
                    );
                }

                StudyPlanSubject::query()->updateOrCreate(
                    [
                        'study_plan_id' => $studyPlan->id,
                        'grade_id' => $grade->id,
                        'subject_id' => $subject->id,
                        'modality_id' => null,
                    ],
                    [
                        'order' => $index + 1,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}