<?php

namespace App\Http\Requests;

use App\Helpers\Telefone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class PessoaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(return_only_nunbers($this->only('cpf_cnpj')));
        $this->merge(Telefone::prepararCampos($this->only('telefone', 'telefone_alt', 'celular')));
        $this->merge([
            'nome_razao' => ($this->get('tipo_pessoa') == 'PJ') ? strtoupper($this->get('nome_razao')) : ucwords(strtolower($this->get('nome_razao'))),
            'nome_fantasia' => strtoupper($this->get('nome_fantasia')),
            'email' => strtolower($this->get('email')),
            'associado' => $this->get('associado') ? 1 : 0,
        ]);

    }

    public function rules(): array
    {
        return [
            'nome_razao' => ['required', 'string', 'max:191', 'unique:pessoas,cpf_cnpj'],
            'nome_fantasia' => ['nullable', 'string', 'max:191'],
            'cpf_cnpj' => ['required', 'cpf_ou_cnpj', 'max:191', 'unique:pessoas,cpf_cnpj,'.$this->route('pessoa')->id],
            'rg_ie' => ['nullable', 'string', 'max:191'],
            'insc_municipal' => ['nullable', 'string', 'max:191'],
            'codigo_contabil' => ['nullable', 'string', 'max:191'],
            'tipo_pessoa' => ['required', 'string', 'max:2'],
            'telefone' => ['nullable', 'celular_com_ddd'],
            'telefone_alt' => ['nullable', 'celular_com_ddd'],
            'celular' => ['nullable', 'celular_com_ddd'],
            'email' => ['nullable', 'email', 'max:191'],
            'site' => ['nullable', 'string', 'max:191'],
            'associado' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome_razao.required' => 'Preencha o campo nome ou razão social',
            'cpf_cnpj.required' => 'Preencha o campo documento',
            'cpf_cnpj.unique' => 'Esse CPF/CNPJ ja foi registrado',
            'telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
            'telefone_alt.celular_com_ddd' => 'Informe um telefone válido com DDD.',
            'celular.celular_com_ddd' => 'Informe um telefone válido com DDD.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user() ?? null,
            'request' => $this->all() ?? null,
            'uri' => $this->fullUrl(),
            'method' => $this->route()->getActionName(),
            'errors' => $validator->errors(),
        ]);
        throw new HttpResponseException(back()->withErrors($validator)->withInput()->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente'));
    }
}
