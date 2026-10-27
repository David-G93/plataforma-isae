<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDivisionCompositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('academic.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'students' => ['present', 'array'],
            'students.*.student_profile_id' => ['required', 'integer', 'distinct', 'exists:student_profiles,id'],
            'students.*.modality_id' => ['nullable', 'integer', 'exists:modalities,id'],
            'teacher_profile_ids' => ['present', 'array'],
            'teacher_profile_ids.*' => ['integer', 'distinct', 'exists:teacher_profiles,id'],
            'staff' => ['present', 'array'],
            'staff.*.person_id' => ['required', 'integer', 'distinct', 'exists:people,id'],
            'staff.*.assignment_role' => ['required', 'string', 'in:gestion,rector,director,vicedirector,secretario,preceptor,tutor,personal'],
        ];
    }
}
