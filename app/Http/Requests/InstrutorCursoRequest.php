<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class InstrutorCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'curso' => ['required', 'numeric', 'exists:cursos,id'],
            'habilitado' => ['required', 'numeric', 'in:0,1'],
            'conhecimento' => ['required', 'numeric', 'in:0,1'],
            'experiencia' => ['required', 'numeric', 'in:0,1'],
            'analise_observacoes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'curso.required' => 'O dado é inválido',
            'curso.numeric' => 'O dado é inválido',
            'curso.exists' => 'O dado é inválido',
            'habilitado.required' => 'O dado é inválido',
            'habilitado.numeric' => 'O dado é inválido',
            'habilitado.in' => 'O dado é inválido',
            'conhecimento.required' => 'O dado é inválido',
            'conhecimento.numeric' => 'O dado é inválido',
            'conhecimento.in' => 'O dado é inválido',
            'experiencia.required' => 'O dado é inválido',
            'experiencia.numeric' => 'O dado é inválido',
            'experiencia.in' => 'O dado é inválido',
            'analise_observacoes.string' => 'O dado é inválido',
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
