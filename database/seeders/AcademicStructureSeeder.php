<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Modality;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            AcademicYear::query()
                ->where('year', '!=', 2026)
                ->update([
                    'is_active' => false,
                ]);

            AcademicYear::query()->updateOrCreate(
                [
                    'year' => 2026,
                ],
                [
                    'name' => 'Ciclo Lectivo 2026',
                    'starts_at' => '2026-03-02',
                    'ends_at' => '2026-12-18',
                    'is_active' => true,
                ],
            );

            $primary = Level::query()->updateOrCreate(
                [
                    'code' => 'primario',
                ],
                [
                    'name' => 'Primario',
                    'order' => 1,
                    'is_active' => true,
                ],
            );

            $secondary = Level::query()->updateOrCreate(
                [
                    'code' => 'secundario',
                ],
                [
                    'name' => 'Secundario',
                    'order' => 2,
                    'is_active' => true,
                ],
            );

            $primaryGrades = [
                [
                    'name' => '1° Grado',
                    'code' => 'primario-1',
                    'order' => 1,
                ],
                [
                    'name' => '2° Grado',
                    'code' => 'primario-2',
                    'order' => 2,
                ],
                [
                    'name' => '3° Grado',
                    'code' => 'primario-3',
                    'order' => 3,
                ],
                [
                    'name' => '4° Grado',
                    'code' => 'primario-4',
                    'order' => 4,
                ],
                [
                    'name' => '5° Grado',
                    'code' => 'primario-5',
                    'order' => 5,
                ],
                [
                    'name' => '6° Grado',
                    'code' => 'primario-6',
                    'order' => 6,
                ],
                [
                    'name' => '7° Grado',
                    'code' => 'primario-7',
                    'order' => 7,
                ],
            ];

            foreach ($primaryGrades as $gradeData) {
                Grade::query()->updateOrCreate(
                    [
                        'level_id' => $primary->id,
                        'code' => $gradeData['code'],
                    ],
                    [
                        'name' => $gradeData['name'],
                        'order' => $gradeData['order'],
                        'is_active' => true,
                    ],
                );
            }

            $secondaryGrades = [
                [
                    'name' => '1° Año',
                    'code' => 'secundario-1',
                    'order' => 1,
                ],
                [
                    'name' => '2° Año',
                    'code' => 'secundario-2',
                    'order' => 2,
                ],
                [
                    'name' => '3° Año',
                    'code' => 'secundario-3',
                    'order' => 3,
                ],
                [
                    'name' => '4° Año',
                    'code' => 'secundario-4',
                    'order' => 4,
                ],
                [
                    'name' => '5° Año',
                    'code' => 'secundario-5',
                    'order' => 5,
                ],
            ];

            foreach ($secondaryGrades as $gradeData) {
                Grade::query()->updateOrCreate(
                    [
                        'level_id' => $secondary->id,
                        'code' => $gradeData['code'],
                    ],
                    [
                        'name' => $gradeData['name'],
                        'order' => $gradeData['order'],
                        'is_active' => true,
                    ],
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Modalidades
            |--------------------------------------------------------------------------
            |
            | Bioinformática es la nueva modalidad que comienza en 1° Año
            | durante 2026 y avanzará año por año con la cohorte.
            |
            | Ciencias Naturales e Informática corresponden al esquema anterior
            | todavía vigente en 2° a 5° Año durante 2026.
            |
            */

            $oldBiotechnology = Modality::query()
                ->where('code', 'biotecnologia')
                ->first();

            $bioinformatics = Modality::query()
                ->where('code', 'bioinformatica')
                ->first();

            if ($oldBiotechnology && ! $bioinformatics) {
                $oldBiotechnology->update([
                    'name' => 'Bioinformática',
                    'code' => 'bioinformatica',
                    'description' => 'Nueva modalidad secundaria iniciada en 2026.',
                    'is_active' => true,
                ]);
            } else {
                Modality::query()->updateOrCreate(
                    [
                        'code' => 'bioinformatica',
                    ],
                    [
                        'name' => 'Bioinformática',
                        'description' => 'Nueva modalidad secundaria iniciada en 2026.',
                        'is_active' => true,
                    ],
                );

                if ($oldBiotechnology) {
                    $oldBiotechnology->update([
                        'is_active' => false,
                    ]);
                }
            }

            Modality::query()->updateOrCreate(
                [
                    'code' => 'ciencias-naturales',
                ],
                [
                    'name' => 'Ciencias Naturales',
                    'description' => 'Modalidad secundaria del plan anterior.',
                    'is_active' => true,
                ],
            );

            Modality::query()->updateOrCreate(
                [
                    'code' => 'informatica',
                ],
                [
                    'name' => 'Informática',
                    'description' => 'Modalidad secundaria del plan anterior.',
                    'is_active' => true,
                ],
            );
        });
    }
}