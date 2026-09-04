<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudyPlanSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('academic.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'study_plan_id' => [
                'required',
                'integer',
                Rule::exists('study_plans', 'id'),
            ],

            'grade_id' => [
                'required',
                'integer',
                Rule::exists('grades', 'id'),
            ],

            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id'),
            ],

            'modality_id' => [
                'nullable',
                'integer',
                Rule::exists('modalities', 'id'),
            ],

            'order' => [
                'required',
                'integer',
                'min:0',
                'max:999',
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
            'study_plan_id.required' =>
                'Seleccioná un plan de estudio.',

            'grade_id.required' =>
                'Seleccioná un grado o año.',

            'subject_id.required' =>
                'Seleccioná una materia.',

            'order.required' =>
                'Ingresá el orden de la materia.',
        ];
    }
}