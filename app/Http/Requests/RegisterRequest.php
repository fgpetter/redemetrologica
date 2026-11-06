<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        Log::channel('register')->info('Tentativa de cadastro', $this->except(['password', 'password_confirmation']));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'document' => ['required', 'cpf'],
            'email' => ['required', 'email', 'max:191', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required_with:password', 'string', 'min:8', 'same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório',
            'name.string' => 'O campo nome deve ser um texto',
            'name.max' => 'O campo nome deve ter no maximo 255 caracteres',
            'document.required' => 'O campo CPF é obrigatório',
            'document.cpf' => 'O CPF não é valido',
            'email.required' => 'O campo email é obrigatório',
            'email.email' => 'O campo email deve ser um email',
            'email.max' => 'O campo email deve ter no maximo 255 caracteres',
            'email.unique' => 'O email informado ja foi registrado',
            'password.required' => 'O campo senha é obrigatório',
            'password.string' => 'O campo senha deve ser um texto',
            'password.min' => 'O campo senha deve ter pelo menos 8 caracteres',
            'password_confirmation.required_with' => 'O campo confirmar senha é obrigatório',
            'password_confirmation.same' => 'As senhas não conferem',

        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user() ?? null,
            'request' => $this->except(['password', 'password_confirmation']),
            'uri' => $this->fullUrl(),
            'method' => $this->route()->getActionName(),
            'errors' => $validator->errors(),
        ]);
        throw new HttpResponseException(back()->withErrors($validator)->withInput($this->except(['password', 'password_confirmation'])));
    }
}
