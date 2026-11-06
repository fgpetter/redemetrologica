<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class DadoBancarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conta_id' => ['nullable', 'exists:dados_bancarios,uid'],
            'pessoa_id' => ['required', 'exists:pessoas,id'],
            'nome_banco' => ['nullable', 'string'],
            'cod_banco' => ['nullable', 'string'],
            'agencia' => ['nullable', 'string'],
            'conta' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome_banco.string' => 'Dado inválido',
            'cod_banco.string' => 'Dado inválido',
            'agencia.string' => 'Dado inválido',
            'conta.string' => 'Dado inválido',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user() ?? null,
            'request' => $this->all() ?? null,
            'uri' => $this->fullUrl(),
            'method' => $this->route()->getActionName(),
            'errors' => $validator->errors(),
        ]);
        throw new HttpResponseException(redirect()->back()->with('error', 'Dados informados não são válidos')->withErrors($validator));
    }
}
