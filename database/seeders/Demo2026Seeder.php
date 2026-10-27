<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GuardianProfile;
use App\Models\Modality;
use App\Models\Person;
use App\Models\SchoolPeriod;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\GenerateTeachingsFromStudyPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class Demo2026Seeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InstitutionalRoleSeeder::class,
            AcademicStructureSeeder::class,
            AcademicYear2026Seeder::class,
            PrimaryStudyPlanSeeder::class,
            SecondaryStudyPlanSeeder::class,
        ]);

        $academicYear = AcademicYear::query()
            ->where('year', 2026)
            ->firstOrFail();

        $naturalSciences = Modality::query()
            ->where('code', 'ciencias-naturales')
            ->firstOrFail();

        $informatics = Modality::query()
            ->where('code', 'informatica')
            ->firstOrFail();

        $this->seedSchoolPeriods($academicYear);

        DB::transaction(function () use ($academicYear, $naturalSciences, $informatics) {
            $staff = $this->seedStaff();
            $teachers = $this->seedTeachers();

            $divisions = Division::query()
                ->with(['course.grade.level'])
                ->whereHas('course.academicYear', fn ($query) => $query->where('year', 2026))
                ->where('is_active', true)
                ->get()
                ->sortBy([
                    ['course.grade.level.order', 'asc'],
                    ['course.grade.order', 'asc'],
                ])
                ->values();

            foreach ($divisions as $divisionIndex => $division) {
                $grade = $division->course->grade;
                $isSecondary = $grade->level->code === 'secundario';
                $studentCount = $isSecondary ? 6 : 3;

                $studentProfiles = collect();

                for ($studentIndex = 1; $studentIndex <= $studentCount; $studentIndex++) {
                    $student = $this->seedStudent(
                        divisionIndex: $divisionIndex,
                        studentIndex: $studentIndex,
                        levelCode: $grade->level->code,
                        gradeCode: $grade->code,
                    );

                    $studentProfiles->push($student);

                    $requiresLegacyModality = $isSecondary
                        && in_array($grade->code, [
                            'secundario-2',
                            'secundario-3',
                            'secundario-4',
                            'secundario-5',
                        ], true);

                    $modalityId = null;

                    if ($requiresLegacyModality) {
                        $modalityId = $studentIndex <= 3
                            ? $naturalSciences->id
                            : $informatics->id;
                    }

                    StudentEnrollment::query()->updateOrCreate(
                        [
                            'student_profile_id' => $student->id,
                            'academic_year_id' => $academicYear->id,
                        ],
                        [
                            'division_id' => $division->id,
                            'modality_id' => $modalityId,
                            'status' => 'active',
                            'enrolled_at' => '2026-03-02',
                            'ended_at' => null,
                            'notes' => 'Datos ficticios de demostración 2026.',
                        ],
                    );
                }

                $divisionTeachers = collect([
                    $teachers[$divisionIndex % count($teachers)],
                    $teachers[($divisionIndex + 1) % count($teachers)],
                    $teachers[($divisionIndex + 4) % count($teachers)],
                    $teachers[($divisionIndex + 7) % count($teachers)],
                ])->unique('id')->values();

                $division->teachers()->syncWithoutDetaching(
                    $divisionTeachers->pluck('id')->all(),
                );

                $staffSync = [
                    $staff['rector']->person_id => ['assignment_role' => 'rector'],
                    $staff['director']->person_id => ['assignment_role' => 'director'],
                    ($isSecondary ? $staff['preceptor_secondary'] : $staff['preceptor_primary'])->person_id => [
                        'assignment_role' => 'preceptor',
                    ],
                ];

                if (! $isSecondary && $divisionTeachers->isNotEmpty()) {
                    $staffSync[$divisionTeachers->first()->person_id] = [
                        'assignment_role' => 'tutor',
                    ];
                }

                $division->staff()->syncWithoutDetaching($staffSync);
            }
        });

        $this->generateAndAssignTeachings();
    }


    private function seedSchoolPeriods(AcademicYear $academicYear): void
    {
        $periods = [
            [
                'order' => 1,
                'name' => '1° Período',
                'starts_at' => '2026-03-02',
                'ends_at' => '2026-05-29',
            ],
            [
                'order' => 2,
                'name' => '2° Período',
                'starts_at' => '2026-06-01',
                'ends_at' => '2026-08-31',
            ],
            [
                'order' => 3,
                'name' => '3° Período',
                'starts_at' => '2026-09-01',
                'ends_at' => '2026-12-18',
            ],
        ];

        foreach ($periods as $period) {
            SchoolPeriod::query()->updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'order' => $period['order'],
                ],
                [
                    'name' => $period['name'],
                    'starts_at' => $period['starts_at'],
                    'ends_at' => $period['ends_at'],
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedStudent(
        int $divisionIndex,
        int $studentIndex,
        string $levelCode,
        string $gradeCode,
    ): StudentProfile {
        $serial = ($divisionIndex * 10) + $studentIndex;
        $dni = (string) (91000000 + $serial);

        $firstNames = [
            'Mateo', 'Sofía', 'Benjamín', 'Valentina', 'Thiago', 'Martina',
            'Bautista', 'Catalina', 'Joaquín', 'Emma', 'Felipe', 'Olivia',
        ];

        $lastNames = [
            'Pérez', 'Gómez', 'Rodríguez', 'López', 'Martínez', 'Díaz',
            'Romero', 'Herrera', 'Torres', 'Castro', 'Vega', 'Molina',
        ];

        $person = Person::query()->updateOrCreate(
            ['dni' => $dni],
            [
                'first_name' => $firstNames[$serial % count($firstNames)],
                'last_name' => $lastNames[($serial + $divisionIndex) % count($lastNames)],
                'birth_date' => $levelCode === 'primario' ? '2016-05-10' : '2011-06-15',
                'email' => "alumno{$serial}@demo.isae.local",
                'phone' => '3804'.str_pad((string) $serial, 6, '0', STR_PAD_LEFT),
                'address' => 'Domicilio ficticio '.$gradeCode,
            ],
        );

        $student = StudentProfile::query()->firstOrCreate([
            'person_id' => $person->id,
        ]);

        $guardianDni = (string) (92000000 + $serial);

        $guardianPerson = Person::query()->updateOrCreate(
            ['dni' => $guardianDni],
            [
                'first_name' => 'Responsable '.$serial,
                'last_name' => $person->last_name,
                'birth_date' => '1984-04-20',
                'email' => "responsable{$serial}@demo.isae.local",
                'phone' => '3805'.str_pad((string) $serial, 6, '0', STR_PAD_LEFT),
                'address' => $person->address,
            ],
        );

        $guardian = GuardianProfile::query()->firstOrCreate([
            'person_id' => $guardianPerson->id,
        ]);

        $student->guardians()->syncWithoutDetaching([
            $guardian->id => [
                'relationship' => $studentIndex % 2 === 0 ? 'Madre' : 'Padre',
                'is_primary' => true,
                'authorized_pickup' => true,
                'receives_communications' => true,
            ],
        ]);

        return $student;
    }

    private function seedTeachers(): array
    {
        $names = [
            ['Ana', 'Acosta'],
            ['Bruno', 'Álvarez'],
            ['Carla', 'Barros'],
            ['Diego', 'Carrizo'],
            ['Elena', 'Domínguez'],
            ['Federico', 'Escudero'],
            ['Gabriela', 'Flores'],
            ['Hernán', 'Gaitán'],
            ['Irene', 'Herrera'],
            ['Javier', 'Luna'],
            ['Karina', 'Méndez'],
            ['Leonardo', 'Navarro'],
        ];

        $teachers = [];

        foreach ($names as $index => [$firstName, $lastName]) {
            $dni = (string) (93000001 + $index);

            $person = Person::query()->updateOrCreate(
                ['dni' => $dni],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => 'docente'.($index + 1).'@demo.isae.local',
                    'phone' => '380600'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'address' => 'Domicilio ficticio docente',
                ],
            );

            $teacher = TeacherProfile::query()->firstOrCreate([
                'person_id' => $person->id,
            ]);

            $user = User::query()->updateOrCreate(
                ['email' => 'docente'.($index + 1).'@demo.isae.local'],
                [
                    'person_id' => $person->id,
                    'name' => $person->full_name,
                    'password' => Hash::make('12345678'),
                    'is_active' => true,
                ],
            );

            $user->syncRoles(['docente']);

            $teachers[] = $teacher;
        }

        return $teachers;
    }

    private function seedStaff(): array
    {
        return [
            'rector' => $this->seedStaffUser(
                dni: '94000001',
                firstName: 'Ricardo',
                lastName: 'Rector Demo',
                role: 'rector',
            ),
            'director' => $this->seedStaffUser(
                dni: '94000002',
                firstName: 'Daniela',
                lastName: 'Directora Demo',
                role: 'director',
            ),
            'preceptor_primary' => $this->seedStaffUser(
                dni: '94000003',
                firstName: 'Pablo',
                lastName: 'Preceptor Primaria',
                role: 'preceptor',
            ),
            'preceptor_secondary' => $this->seedStaffUser(
                dni: '94000004',
                firstName: 'Paula',
                lastName: 'Preceptora Secundaria',
                role: 'preceptor',
            ),
        ];
    }

    private function seedStaffUser(
        string $dni,
        string $firstName,
        string $lastName,
        string $role,
    ): User {
        $person = Person::query()->updateOrCreate(
            ['dni' => $dni],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($role).'.'.$dni.'@demo.isae.local',
                'phone' => '3807000000',
                'address' => 'ISAE - dato ficticio',
            ],
        );

        $user = User::query()->updateOrCreate(
            ['email' => strtolower($role).'.'.$dni.'@demo.isae.local'],
            [
                'person_id' => $person->id,
                'name' => $person->full_name,
                'password' => Hash::make('12345678'),
                'is_active' => true,
            ],
        );

        $user->syncRoles([$role]);

        return $user;
    }

    private function generateAndAssignTeachings(): void
    {
        $generator = app(GenerateTeachingsFromStudyPlan::class);

        $divisions = Division::query()
            ->with(['course.academicYear', 'teachers'])
            ->whereHas('course.academicYear', fn ($query) => $query->where('year', 2026))
            ->where('is_active', true)
            ->get();

        foreach ($divisions as $division) {
            $teachings = $generator->handle($division);
            $teacherIds = $division->teachers()->pluck('teacher_profiles.id')->values();

            if ($teacherIds->isEmpty()) {
                throw new RuntimeException(
                    "La división {$division->course?->name} no tiene docentes demo asignados.",
                );
            }

            foreach ($teachings->values() as $index => $teaching) {
                $assigned = [
                    $teacherIds[$index % $teacherIds->count()],
                ];

                // Algunas cursadas quedan deliberadamente con dos docentes
                // para validar el soporte de co-docencia.
                if ($index % 7 === 0 && $teacherIds->count() > 1) {
                    $assigned[] = $teacherIds[($index + 1) % $teacherIds->count()];
                }

                $teaching->teachers()->sync(array_values(array_unique($assigned)));
            }
        }
    }
}
