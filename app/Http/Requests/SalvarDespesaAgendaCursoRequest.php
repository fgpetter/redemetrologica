<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SalvarDespesaAgendaCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agenda_curso_id' => ['nullable', 'exists:agenda_cursos,id'],
            'material_padrao' => ['required', 'exists:materiais_padroes,id'],
            'quantidade' => ['required', 'regex:/[\d.,]+$/'],
            'valor' => ['required', 'regex:/[\d.,]+$/'],
            'total' => ['required', 'regex:/[\d.,]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'agenda_curso_id.exists' => 'Houve um erro ao editar despesa. Tente novamente',
            'material_padrao.exists' => 'Selecione uma opção válida',
            'quantidade.regex' => 'O dado enviado não é valido',
            'quantidade.required' => 'Preencha o campo',
            'valor.required' => 'Preencha o campo',
            'valor.regex' => 'Não é um número válido',
            'total.required' => 'O campo não pode estar vazio',
            'total.regex' => 'Não é um número válido',
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
