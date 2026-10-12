<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncTeachingTeachersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('academic.manage')
            ?? false;
    }

    public function rules(): array
    {
        return [
            'teacher_profile_ids' => [
                'present',
                'array',
            ],

            'teacher_profile_ids.*' => [
                'integer',
                'distinct',
                'exists:teacher_profiles,id',
            ],
        ];
    }
}