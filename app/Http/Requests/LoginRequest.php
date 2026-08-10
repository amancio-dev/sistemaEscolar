<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $userType = $this->userType();

        return [
            'tipo_usuario' => ['required', Rule::in(['administrador', 'professor', 'aluno'])],
            'email' => ['required', 'string', 'email'],
            'password' => [
                Rule::excludeIf($userType !== 'administrador'),
                Rule::requiredIf($userType === 'administrador'),
                'string',
            ],
            'cpf' => [
                Rule::excludeIf(! in_array($userType, ['professor', 'aluno'], true)),
                Rule::requiredIf(in_array($userType, ['professor', 'aluno'], true)),
                'string',
                'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/',
            ],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'cpf.regex' => 'Informe um CPF válido, no formato 000.000.000-00.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'tipo_usuario' => 'perfil de acesso',
            'email' => 'e-mail',
            'password' => 'senha',
            'cpf' => 'CPF',
        ];
    }

    public function userType(): string
    {
        return (string) $this->input('tipo_usuario', '');
    }

    public function cpfDigits(): string
    {
        return (string) preg_replace('/\D/', '', (string) $this->input('cpf', ''));
    }
}
