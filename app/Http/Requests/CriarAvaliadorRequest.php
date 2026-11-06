<?php

namespace App\Http\Requests;

use App\Helpers\Telefone;
use App\Models\Pessoa;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CriarAvaliadorRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(return_only_nunbers($this->only('cpf_cnpj')));
        $this->merge(['telefone' => Telefone::paraValidacao($this->input('telefone'))]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pessoa = Pessoa::where('uid', $this->pessoa_uid)->first();

        return [
            'pessoa_uid' => ['required', 'string', 'exists:pessoas,uid'],
            'cpf_cnpj' => ['required', 'string', 'max:191', 'unique:pessoas,cpf_cnpj,'.$pessoa?->id], // TODO - adicionar validação de CPF/CNPJ
            'telefone' => ['nullable', 'celular_com_ddd'],
            'rg_ie' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'exp_min_comprovada' => ['nullable', 'boolean'],
            'curso_incerteza' => ['nullable', 'boolean'],
            'curso_iso' => ['nullable', 'boolean'],
            'curso_aud_interna' => ['nullable', 'boolean'],
            'parecer_psicologico' => ['nullable', 'boolean'],
            'curriculo' => ['file', 'mimes:doc,pdf,docx', 'max:5242880'], // 5mb
            'data_ingresso' => ['nullable', Rule::date()->format('Y-m-d')],
            'situacao' => ['required', 'string', Rule::in(['ATIVO', 'AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LIDER', 'ESPECIALISTA', 'INATIVO'])],
        ];
    }

    public function messages(): array
    {
        return [
            'pessoa_uid.required' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
            'pessoa_uid.string' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
            'pessoa_uid.exists' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
            'cpf_cnpj.required' => 'Preencha o campo CPF',
            'cpf_cnpj.min' => 'CPF inválido',
            'cpf_cnpj.max' => 'CPF inválido',
            'telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
            'curriculo.mimes' => 'Somente arquivos DOC, DOCX e PDF',
            'curriculo.max' => 'Tamanho máximo 5MB',
            'situacao.required' => 'Selecione uma situação.',
            'situacao.in' => 'Selecione uma situação válida.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', ['user' => auth()->user(), 'request' => $this->all(), 'uri' => $this->fullUrl(), 'method' => static::class, 'errors' => $validator->errors()]);
        parent::failedValidation($validator);
    }
}
