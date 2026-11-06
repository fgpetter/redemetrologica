<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FiltrarLancamentosAReceberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_inicial' => ['nullable', Rule::date()->format('Y-m-d')],
            'data_final' => ['nullable', Rule::date()->format('Y-m-d')],
            'pessoa' => ['nullable', 'exists:pessoas,id'],
            'area' => ['nullable', 'in:CURSO,PEP,AVALIACAO'],
            'curso' => ['nullable', 'exists:agenda_cursos,id', Rule::prohibitedIf(boolval($this->pep))],
            'pep' => ['nullable', 'exists:agenda_interlabs,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'curso.prohibited' => 'Essa seleção é conflituosa',
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
