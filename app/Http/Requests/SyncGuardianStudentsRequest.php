<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncGuardianStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('people.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'students' => [
                'present',
                'array',
            ],

            'students.*.student_profile_id' => [
                'required',
                'integer',
                'distinct',
                'exists:student_profiles,id',
            ],

            'students.*.relationship' => [
                'nullable',
                'string',
                'max:50',
            ],

            'students.*.is_primary' => [
                'required',
                'boolean',
            ],

            'students.*.authorized_pickup' => [
                'required',
                'boolean',
            ],

            'students.*.receives_communications' => [
                'required',
                'boolean',
            ],
        ];
    }
}