<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Person;
use App\Models\Teaching;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ImportTeachingTeachers extends Command
{
    protected $signature = 'isae:import-teaching-teachers
        {file : Ruta al archivo CSV}
        {--replace : Reemplaza los docentes actuales de cada dictado}';

    protected $description =
        'Importa docentes a los dictados usando DNI, ciclo, grado/año, materia y modalidad.';

    public function handle(): int
    {
        $path = $this->resolvePath(
            $this->argument('file'),
        );

        if (! is_file($path)) {
            $this->error(
                "No existe el archivo: {$path}",
            );

            return self::FAILURE;
        }

        try {
            $rows = $this->readCsv($path);

            if ($rows->isEmpty()) {
                throw new RuntimeException(
                    'El archivo no contiene filas para importar.',
                );
            }

            $assignments = $this->validateAndResolve(
                $rows,
            );

            DB::transaction(function () use ($assignments) {
                $grouped = $assignments->groupBy(
                    'teaching_id',
                );

                foreach ($grouped as $teachingId => $items) {
                    $teacherIds = $items
                        ->pluck('teacher_profile_id')
                        ->unique()
                        ->values()
                        ->all();

                    $teaching = Teaching::query()
                        ->with('division')
                        ->findOrFail($teachingId);

                    // Un docente asignado a un dictado pasa también a formar parte
                    // del plantel docente habilitado de esa división.
                    $teaching->division
                        ->teachers()
                        ->syncWithoutDetaching($teacherIds);

                    if ($this->option('replace')) {
                        $teaching
                            ->teachers()
                            ->sync($teacherIds);

                        continue;
                    }

                    $teaching
                        ->teachers()
                        ->syncWithoutDetaching(
                            $teacherIds,
                        );
                }
            });

            $this->newLine();

            $this->info(
                'Importación finalizada correctamente.',
            );

            $this->table(
                [
                    'Dato',
                    'Cantidad',
                ],
                [
                    [
                        'Filas procesadas',
                        $rows->count(),
                    ],
                    [
                        'Asignaciones',
                        $assignments->count(),
                    ],
                    [
                        'Dictados afectados',
                        $assignments
                            ->pluck('teaching_id')
                            ->unique()
                            ->count(),
                    ],
                    [
                        'Docentes distintos',
                        $assignments
                            ->pluck('teacher_profile_id')
                            ->unique()
                            ->count(),
                    ],
                ],
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->newLine();

            $this->error(
                $exception->getMessage(),
            );

            return self::FAILURE;
        }
    }

    private function resolvePath(
        string $file,
    ): string {
        if (
            str_starts_with($file, '/')
            || preg_match(
                '/^[A-Za-z]:[\\\\\\/]/',
                $file,
            )
        ) {
            return $file;
        }

        return base_path($file);
    }

    private function readCsv(
        string $path,
    ): Collection {
        $handle = fopen(
            $path,
            'r',
        );

        if ($handle === false) {
            throw new RuntimeException(
                'No se pudo abrir el archivo CSV.',
            );
        }

        $headers = fgetcsv(
            $handle,
            separator: ';',
        );

        if ($headers === false) {
            fclose($handle);

            throw new RuntimeException(
                'El CSV no tiene encabezados.',
            );
        }

        $headers = array_map(
            fn ($header) => trim(
                mb_strtolower(
                    (string) $header,
                ),
            ),
            $headers,
        );

        $requiredHeaders = [
            'year',
            'grade_code',
            'subject_code',
            'modality_code',
            'teacher_dni',
        ];

        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array(
                $requiredHeader,
                $headers,
                true,
            )) {
                fclose($handle);

                throw new RuntimeException(
                    "Falta la columna {$requiredHeader} en el CSV.",
                );
            }
        }

        $rows = collect();

        $line = 1;

        while (
            (
                $data = fgetcsv(
                    $handle,
                    separator: ';',
                )
            ) !== false
        ) {
            $line++;

            $hasData = collect($data)
                ->contains(
                    fn ($value) =>
                        trim((string) $value) !== '',
                );

            if (! $hasData) {
                continue;
            }

            if (
                count($data)
                !== count($headers)
            ) {
                fclose($handle);

                throw new RuntimeException(
                    "La línea {$line} tiene una cantidad incorrecta de columnas.",
                );
            }

            $row = array_combine(
                $headers,
                $data,
            );

            $row = array_map(
                fn ($value) => trim(
                    (string) $value,
                ),
                $row,
            );

            $row['_line'] = $line;

            $rows->push($row);
        }

        fclose($handle);

        return $rows;
    }

    private function validateAndResolve(
        Collection $rows,
    ): Collection {
        $resolved = collect();
        $errors = [];

        foreach ($rows as $row) {
            try {
                $resolved->push(
                    $this->resolveRow($row),
                );
            } catch (Throwable $exception) {
                $errors[] = sprintf(
                    'Línea %d: %s',
                    $row['_line'],
                    $exception->getMessage(),
                );
            }
        }

        if ($errors !== []) {
            throw new RuntimeException(
                "La importación fue cancelada porque hay errores:\n\n"
                .implode("\n", $errors),
            );
        }

        return $resolved;
    }

    private function resolveRow(
        array $row,
    ): array {
        $year = (int) $row['year'];

        $academicYear = AcademicYear::query()
            ->where('year', $year)
            ->first();

        if (! $academicYear) {
            throw new RuntimeException(
                "No existe el ciclo lectivo {$year}.",
            );
        }

        $gradeCode = $row['grade_code'];

        $grade = Grade::query()
            ->where('code', $gradeCode)
            ->first();

        if (! $grade) {
            throw new RuntimeException(
                "No existe {$gradeCode}.",
            );
        }

        $subjectCode = $row['subject_code'];
        $modalityCode = $row['modality_code'];

        $query = Teaching::query()
            ->with([
                'subject',
                'modality',
                'division.course',
            ])
            ->where('is_active', true)
            ->whereHas(
                'subject',
                fn ($query) => $query->where(
                    'code',
                    $subjectCode,
                ),
            )
            ->whereHas(
                'division.course',
                fn ($query) => $query
                    ->where(
                        'academic_year_id',
                        $academicYear->id,
                    )
                    ->where(
                        'grade_id',
                        $grade->id,
                    ),
            );

        if ($modalityCode === '') {
            $query->whereNull(
                'modality_id',
            );
        } else {
            $query->whereHas(
                'modality',
                fn ($query) => $query->where(
                    'code',
                    $modalityCode,
                ),
            );
        }

        $teachings = $query->get();

        if ($teachings->isEmpty()) {
            $description =
                "{$year} / {$gradeCode} / {$subjectCode}";

            if ($modalityCode !== '') {
                $description .=
                    " / {$modalityCode}";
            }

            throw new RuntimeException(
                "No existe el dictado {$description}.",
            );
        }

        if ($teachings->count() > 1) {
            throw new RuntimeException(
                "Se encontraron varios dictados para {$gradeCode} / {$subjectCode}.",
            );
        }

        $dni = preg_replace(
            '/\D+/',
            '',
            $row['teacher_dni'],
        );

        if ($dni === '') {
            throw new RuntimeException(
                'El DNI del docente está vacío.',
            );
        }

        $person = Person::query()
            ->with('teacherProfile')
            ->where('dni', $dni)
            ->first();

        if (! $person) {
            throw new RuntimeException(
                "No existe una persona con DNI {$dni}.",
            );
        }

        if (! $person->teacherProfile) {
            throw new RuntimeException(
                "{$person->full_name} ({$dni}) no tiene perfil docente.",
            );
        }

        return [
            'teaching_id' =>
                $teachings->first()->id,

            'teacher_profile_id' =>
                $person->teacherProfile->id,
        ];
    }
}