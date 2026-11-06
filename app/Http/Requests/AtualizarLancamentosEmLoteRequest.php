<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AtualizarLancamentosEmLoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uids' => ['required', 'array', 'min:1'],
            'uids.*' => ['required', 'string', 'exists:lancamentos_financeiros,uid'],
            'consiliacao' => ['nullable', 'string', 'max:191'],
            'nota_fiscal' => ['nullable', 'string', 'max:191'],
            'data_pagamento' => ['nullable', Rule::date()->format('Y-m-d')],
            'data_vencimento' => ['nullable', Rule::date()->format('Y-m-d')],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user(),
            'request' => $this->all(),
            'uri' => $this->fullUrl(),
            'method' => static::class,
            'errors' => $validator->errors(),
        ]);

        parent::failedValidation($validator);
    }
}
