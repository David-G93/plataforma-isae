<?php

namespace App\Http\Requests;

use App\Models\Person;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonInstitutionalAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('people.manage') ?? false;
    }

    public function rules(): array
    {
        /** @var Person|null $person */
        $person = $this->route('person');

        $user = $person?->user;

        $emailRules = [
            'nullable',
            'email',
            'max:255',

            Rule::unique('users', 'email')
                ->ignore($user?->id),
        ];

        if ($user || $this->boolean('is_active')) {
            $emailRules[0] = 'required';
        }

        $passwordRules = [
            'nullable',
            'string',
            'min:8',
            'max:255',
        ];

        if (! $user && $this->boolean('is_active')) {
            $passwordRules[0] = 'required';
        }

        return [
            'student' => [
                'required',
                'boolean',
            ],

            'teacher' => [
                'required',
                'boolean',
            ],

            'guardian' => [
                'required',
                'boolean',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'email' => $emailRules,

            'password' => $passwordRules,
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' =>
                'Ingresá un email para la cuenta de acceso.',

            'email.email' =>
                'Ingresá un email válido.',

            'email.unique' =>
                'Ese email ya pertenece a otro usuario.',

            'password.required' =>
                'Ingresá una contraseña inicial.',

            'password.min' =>
                'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}