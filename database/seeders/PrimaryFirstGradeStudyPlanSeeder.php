<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use RuntimeException;

class PrimaryFirstGradeStudyPlanSeeder extends Seeder
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

        $grade = Grade::query()
            ->where('code', 'primario-1')
            ->first();

        if (! $grade) {
            throw new RuntimeException(
                'No existe 1° Grado con código primario-1.',
            );
        }

        if ($studyPlan->level_id !== $grade->level_id) {
            throw new RuntimeException(
                'El Plan Primario no corresponde al nivel de 1° Grado.',
            );
        }

        $subjects = [
            [
                'name' => 'C.C y Educación para la Vida',
                'code' => 'cc-educacion-para-la-vida',
            ],
            [
                'name' => 'Ciencias Naturales',
                'code' => 'ciencias-naturales',
            ],
            [
                'name' => 'Ciencias Sociales',
                'code' => 'ciencias-sociales',
            ],
            [
                'name' => 'Danza',
                'code' => 'danza',
            ],
            [
                'name' => 'Educación Física',
                'code' => 'educacion-fisica',
            ],
            [
                'name' => 'Educación Tecnológica',
                'code' => 'educacion-tecnologica',
            ],
            [
                'name' => 'Informática',
                'code' => 'informatica',
            ],
            [
                'name' => 'Inglés',
                'code' => 'ingles',
            ],
            [
                'name' => 'Lengua y Literatura',
                'code' => 'lengua-y-literatura',
            ],
            [
                'name' => 'Matemática',
                'code' => 'matematica',
            ],
            [
                'name' => 'Música',
                'code' => 'musica',
            ],
            [
                'name' => 'Plástica Visual',
                'code' => 'plastica-visual',
            ],
            [
                'name' => 'Robótica',
                'code' => 'robotica',
            ],
            [
                'name' => 'Taller de lectura',
                'code' => 'taller-de-lectura',
            ],
            [
                'name' => 'Teatro',
                'code' => 'teatro',
            ],
        ];

        foreach ($subjects as $index => $subjectData) {
            $subject = Subject::query()->updateOrCreate(
                [
                    'code' => $subjectData['code'],
                ],
                [
                    'name' => $subjectData['name'],
                    'description' => null,
                    'is_active' => true,
                ],
            );

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