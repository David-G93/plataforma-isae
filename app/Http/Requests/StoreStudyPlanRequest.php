<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudyPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('academic.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'level_id' => [
                'required',
                'integer',
                Rule::exists('levels', 'id'),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('study_plans', 'code'),
            ],

            'effective_from_year' => [
                'nullable',
                'integer',
                'min:2000',
                'max:2100',
            ],

            'effective_to_year' => [
                'nullable',
                'integer',
                'min:2000',
                'max:2100',
                'gte:effective_from_year',
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
            'level_id.required' =>
                'Seleccioná un nivel.',

            'name.required' =>
                'Ingresá el nombre del plan.',

            'code.required' =>
                'Ingresá un código para el plan.',

            'code.unique' =>
                'Ya existe un plan con ese código.',

            'effective_to_year.gte' =>
                'El año final no puede ser anterior al año de inicio.',
        ];
    }
}