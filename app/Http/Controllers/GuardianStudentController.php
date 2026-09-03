<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncGuardianStudentsRequest;
use App\Models\GuardianProfile;
use Illuminate\Http\RedirectResponse;

class GuardianStudentController extends Controller
{
    public function update(
        SyncGuardianStudentsRequest $request,
        GuardianProfile $guardianProfile,
    ): RedirectResponse {
        $students = collect(
            $request->validated('students'),
        )->mapWithKeys(function (array $student) {
            return [
                $student['student_profile_id'] => [
                    'relationship' => $student['relationship'] ?: null,
                    'is_primary' => $student['is_primary'],
                    'authorized_pickup' => $student['authorized_pickup'],
                    'receives_communications' => $student['receives_communications'],
                ],
            ];
        })->all();

        $guardianProfile->students()->sync($students);

        return redirect()
            ->route(
                'people.show',
                $guardianProfile->person_id,
            )
            ->with(
                'success',
                'Estudiantes vinculados correctamente.',
            );
    }
}