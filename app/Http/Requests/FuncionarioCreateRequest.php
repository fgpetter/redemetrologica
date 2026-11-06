<?php

namespace App\Http\Requests;

use App\Helpers\Telefone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FuncionarioCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['telefone' => Telefone::paraValidacao($this->input('telefone'))]);
    }

    public function rules(): array
    {
        return [
            'rg_ie' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'end_padrao' => ['nullable', 'boolean'],
            'cargo' => ['nullable', 'string', 'max:255'],
            'setor' => ['nullable', 'string', 'max:255'],
            'demissao' => ['nullable', 'date_format:Y-m-d'],
            'observacoes' => ['nullable', 'string'],
            'nome_razao' => ['required', 'string', 'max:191'],
            'cpf_cnpj' => ['required', 'string', 'max:191', 'unique:pessoas,cpf_cnpj,'], // TODO - adicionar validação de CPF/CNPJ
            'cep' => ['required', 'string', 'max:9', 'min:9'],
            'endereco' => ['required', 'string', 'max:191'],
            'cidade' => ['required', 'string', 'max:191'],
            'uf' => ['required', 'string', 'max:2', 'min:2'],
            'telefone' => ['nullable', 'celular_com_ddd'],
            'admissao' => ['required', Rule::date()->format('Y-m-d')],
            'curriculo' => ['file', 'mimes:doc,pdf,docx', 'max:5242880'], // 5mb
        ];
    }

    public function messages(): array
    {
        return [
            'nome_razao.required' => 'Preencha o campo nome ou razão social',
            'cpf_cnpj.required' => 'Preencha o campo CPF',
            'cpf_cnpj.min' => 'CPF inválido',
            'cpf_cnpj.max' => 'CPF inválido',
            'cep.required' => 'Preencha o campo CEP',
            'cep.min' => 'CEP inválido',
            'cep.mmax' => 'CEP inválido',
            'endereco.required' => 'Preencha o campo endereco',
            'cidade.required' => 'Preencha o campo cidade',
            'uf.required' => 'Inválido',
            'uf.min' => 'Inválido',
            'uf.max' => 'Inválido',
            'telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
            'admissao.required' => 'Preencha o campo admissão',
            'admissao.date' => 'A data não é válida',
            'admissao.date_format' => 'A data não é válida',
            'curriculo.mimes' => 'Somente arquivos DOC, DOCX e PDF',
            'curriculo.max' => 'Tamanho máximo 5MB',
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
