<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class BancoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_banco' => ['nullable', 'string'],
            'nome_banco' => ['nullable', 'string'],
            'agencia' => ['nullable', 'string'],
            'conta' => ['nullable', 'string'],
            'movimenta_financeiro' => ['nullable', 'string'],
            'padrao' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_banco.string' => 'Conteúdo inválido',
            'nome_banco.string' => 'Conteúdo inválido',
            'agencia.string' => 'Conteúdo inválido',
            'conta.string' => 'Conteúdo inválido',
            'movimenta_financeiro.string' => 'Conteúdo inválido',
            'padrao.string' => 'Conteúdo inválido',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user() ?? null,
            'uri' => $this->fullUrl(),
            'method' => $this->route()->getActionName(),
            'errors' => $validator->errors(),
        ]);

        parent::failedValidation($validator);
    }
}
