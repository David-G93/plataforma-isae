<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('academic.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id'),
            ],

            'grade_id' => [
                'required',
                'integer',
                Rule::exists('grades', 'id'),
            ],

            'study_plan_id' => [
                'nullable',
                'integer',
                Rule::exists('study_plans', 'id'),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' =>
                'Seleccioná un ciclo lectivo.',

            'grade_id.required' =>
                'Seleccioná un grado o año.',

            'name.required' =>
                'Ingresá un nombre para el curso.',
        ];
    }
}