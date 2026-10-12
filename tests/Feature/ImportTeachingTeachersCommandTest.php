<?php

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Person;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\Teaching;
use App\Services\GenerateTeachingsFromStudyPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

afterEach(function () {
    File::delete(
        storage_path(
            'app/imports/test-docentes-dictados.csv',
        ),
    );
});

test('it imports teacher into teaching from csv', function () {
    $context = createTeachingImportContext();

    $person = Person::factory()->create([
        'dni' => '30111222',
        'first_name' => 'Ana',
        'last_name' => 'Docente',
    ]);

    $teacher = TeacherProfile::factory()->create([
        'person_id' => $person->id,
    ]);

    File::ensureDirectoryExists(
        storage_path('app/imports'),
    );

    $path = storage_path(
        'app/imports/test-docentes-dictados.csv',
    );

    File::put(
        $path,
        implode("\n", [
            'year;grade_code;subject_code;modality_code;teacher_dni',
            '2026;secundario-3;matematica;;30111222',
        ]),
    );

    $this->artisan(
        'isae:import-teaching-teachers',
        [
            'file' => $path,
        ],
    )->assertSuccessful();

    expect(
        $context['teaching']
            ->fresh()
            ->teachers
            ->pluck('id')
            ->all(),
    )->toContain(
        $teacher->id,
    );
});

test('it allows several teachers in same teaching', function () {
    $context = createTeachingImportContext();

    $teacherOnePerson = Person::factory()->create([
        'dni' => '30111222',
    ]);

    $teacherOne = TeacherProfile::factory()->create([
        'person_id' => $teacherOnePerson->id,
    ]);

    $teacherTwoPerson = Person::factory()->create([
        'dni' => '30222333',
    ]);

    $teacherTwo = TeacherProfile::factory()->create([
        'person_id' => $teacherTwoPerson->id,
    ]);

    File::ensureDirectoryExists(
        storage_path('app/imports'),
    );

    $path = storage_path(
        'app/imports/test-docentes-dictados.csv',
    );

    File::put(
        $path,
        implode("\n", [
            'year;grade_code;subject_code;modality_code;teacher_dni',
            '2026;secundario-3;matematica;;30111222',
            '2026;secundario-3;matematica;;30222333',
        ]),
    );

    $this->artisan(
        'isae:import-teaching-teachers',
        [
            'file' => $path,
        ],
    )->assertSuccessful();

    $teacherIds = $context['teaching']
        ->fresh()
        ->teachers
        ->pluck('id');

    expect($teacherIds)
        ->toHaveCount(2)
        ->toContain($teacherOne->id)
        ->toContain($teacherTwo->id);
});

test('import fails when teacher does not exist', function () {
    createTeachingImportContext();

    File::ensureDirectoryExists(
        storage_path('app/imports'),
    );

    $path = storage_path(
        'app/imports/test-docentes-dictados.csv',
    );

    File::put(
        $path,
        implode("\n", [
            'year;grade_code;subject_code;modality_code;teacher_dni',
            '2026;secundario-3;matematica;;99999999',
        ]),
    );

    $this->artisan(
        'isae:import-teaching-teachers',
        [
            'file' => $path,
        ],
    )->assertFailed();
});

function createTeachingImportContext(): array
{
    $academicYear = AcademicYear::factory()->create([
        'year' => 2026,
        'name' => 'Ciclo Lectivo 2026',
    ]);

    $level = Level::factory()->create([
        'name' => 'Secundario',
        'code' => 'secundario',
    ]);

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
        'name' => '3° Año',
        'code' => 'secundario-3',
    ]);

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $level->id,
        'name' => 'Secundario',
        'code' => 'secundario-test',
        'is_active' => true,
    ]);

    $course = Course::factory()->create([
        'academic_year_id' => $academicYear->id,
        'grade_id' => $grade->id,
        'study_plan_id' => $studyPlan->id,
        'name' => '3° Año',
    ]);

    $division = Division::factory()->create([
        'course_id' => $course->id,
        'name' => 'Única',
        'is_active' => true,
    ]);

    $subject = Subject::factory()->create([
        'name' => 'Matemática',
        'code' => 'matematica',
    ]);

    StudyPlanSubject::factory()->create([
        'study_plan_id' => $studyPlan->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'modality_id' => null,
        'is_active' => true,
    ]);

    $teaching = app(
        GenerateTeachingsFromStudyPlan::class,
    )->handle(
        $division,
    )->first();

    return [
        'academicYear' => $academicYear,
        'level' => $level,
        'grade' => $grade,
        'studyPlan' => $studyPlan,
        'course' => $course,
        'division' => $division,
        'subject' => $subject,
        'teaching' => $teaching,
    ];
}