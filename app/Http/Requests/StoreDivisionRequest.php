<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('academic.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => [
                'required',
                'integer',
                Rule::exists('courses', 'id'),
            ],

            'name' => [
                'required',
                'string',
                'max:50',

                Rule::unique('divisions', 'name')
                    ->where(
                        fn ($query) => $query->where(
                            'course_id',
                            $this->integer('course_id'),
                        ),
                    ),
            ],

            'shift' => [
                'nullable',
                'string',
                'max:50',
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
            'course_id.required' =>
                'Seleccioná un curso.',

            'name.required' =>
                'Ingresá el nombre de la división.',

            'name.unique' =>
                'Ya existe esa división dentro del curso.',
        ];
    }
}