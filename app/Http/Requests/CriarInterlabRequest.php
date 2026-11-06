<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class CriarInterlabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:190'],
            'descricao' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'in:BILATERAL,INTERLABORATORIAL'],
            'avaliacao' => ['nullable', 'string', 'in:LABORATORIAL,ANALISTA'],
            'tag' => ['required', 'min:3', 'max:5'],
            'thumb' => ['nullable', 'string'],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Preencha o campo Nome',
            'nome.string' => 'O campo aceita somente texto.',
            'nome.max' => 'O campo aceita no maximo 190 caracteres.',
            'descricao.string' => 'O campo aceita somente texto.',
            'tipo.in' => 'A opção selecionada é inválida',
            'tag.required' => 'Preencha o campo TAG',
            'tag.min' => 'O campo TAG deve ter no mínimo 3 caracteres.',
            'tag.max' => 'O campo TAG deve ter no máximo 5 caracteres.',
            'observacoes' => 'O campo aceita somente texto.',
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
