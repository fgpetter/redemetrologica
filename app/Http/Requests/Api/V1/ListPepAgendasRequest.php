<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPepAgendasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(['agendado', 'confirmado', 'concluido'])],
            'ano' => ['nullable', 'integer'],
            'datainicio' => ['nullable', 'date'],
            'datafim' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'O status deve ser agendado, confirmado ou concluido.',
            'ano.integer' => 'O campo ano deve ser um número inteiro.',
            'datainicio.date' => 'O campo datainicio deve ser uma data válida.',
            'datafim.date' => 'O campo datafim deve ser uma data válida.',
        ];
    }
}
