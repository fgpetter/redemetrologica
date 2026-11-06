<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class InstrutorUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cpf_cnpj' => ['required', 'string', 'max:191', 'unique:pessoas,cpf_cnpj,'.$this->route('instrutor')->pessoa->id],
            'rg_ie' => ['required', 'string'],
            'nome' => ['required', 'string'],
            'tipo_pessoa' => ['required', 'in:PF,PJ'],
            'situacao' => ['required', 'integer'],
            'curriculo' => ['file', 'mimes:doc,pdf,docx', 'max:5242880'], // 5mb
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome não pode ficar em branco',
            'nome.string' => 'O dado enviado não é válido',
            'cpf_cnpj.required' => 'O CPF/CNPJ não pode ficar em branco',
            'cpf_cnpj.string' => 'O dado enviado não é válido',
            'rg_ie.required' => 'O RG/IE não pode ficar em branco',
            'rg_ie.string' => 'O dado enviado não é válido',
            'situacao.required' => 'Selecione uma opção válida',
            'situacao.integer' => 'Selecione uma opção válida',
            'curriculo.mimes' => 'Arquivo inválido',
            'curriculo.max' => 'O arquivo é muito grande, tamanho máximo 5mb',
            'observacoes.string' => 'O dado enviado não é válido',
            'tipo_pessoa.required' => 'O dado enviado não é válido',
            'tipo_pessoa.in' => 'O dado enviado não é válido',
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
