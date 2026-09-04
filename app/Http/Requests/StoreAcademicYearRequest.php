<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('academic.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('academic_years', 'year'),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'starts_at' => [
                'required',
                'date',
            ],

            'ends_at' => [
                'required',
                'date',
                'after:starts_at',
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
            'year.required' => 'Ingresá el año del ciclo lectivo.',
            'year.unique' => 'Ya existe un ciclo lectivo para ese año.',
            'name.required' => 'Ingresá un nombre para el ciclo lectivo.',
            'starts_at.required' => 'Ingresá la fecha de inicio.',
            'ends_at.required' => 'Ingresá la fecha de finalización.',
            'ends_at.after' => 'La fecha de finalización debe ser posterior al inicio.',
        ];
    }
}