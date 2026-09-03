<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('people.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'dni' => [
                'required',
                'string',
                'max:20',

                Rule::unique('people', 'dni')
                    ->ignore($this->route('person')),
            ],

            'first_name' => [
                'required',
                'string',
                'max:255',
            ],

            'last_name' => [
                'required',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'dni' => trim((string) $this->dni),
            'first_name' => trim((string) $this->first_name),
            'last_name' => trim((string) $this->last_name),
        ]);
    }
}