<?php

namespace App\Http\Requests;

use App\Helpers\Telefone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class AtualizarLaboratorioRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(Telefone::prepararCampos($this->only('telefone')));
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome_laboratorio' => ['nullable', 'string'],
            'telefone' => ['nullable', 'celular_com_ddd'],
            'email' => ['nullable', 'email'],
            'responsavel_tecnico' => ['nullable', 'string'],
            'contato' => ['nullable', 'string'],
            'cod_laboratorio' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome_laboratorio.string' => 'O dado enviado não é válido',
            'telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
            'email.email' => 'O dado enviado não é válido',
            'responsavel_tecnico.string' => 'O dado enviado não é válido',
            'contato.string' => 'O dado enviado não é válido',
            'cod_laboratorio.string' => 'Código inválido',
        ];
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
