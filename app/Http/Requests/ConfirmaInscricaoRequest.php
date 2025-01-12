<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmaInscricaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'telefone' => ['required', 'celular_com_ddd'],
            'cpf_cnpj' => ['required', 'cpf'],
            'id_empresa' => ['nullable', 'exists:pessoas,id'],
            'id_pessoa' => ['required', 'exists:pessoas,id'],
            'convidado' => ['nullable', 'boolean'],

            'id_endereco' => ['nullable', 'exists:enderecos,uid'],
            'cep' => ['required_without:id_empresa', 'string'],
            'uf' => ['required_without:id_empresa', 'string'],
            'endereco' => ['required_without:id_empresa', 'string'],
            'complemento' => ['nullable', 'string'],
            'bairro' => ['nullable', 'string'],
            'cidade' => ['required_without:id_empresa', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'Preencha o campo nome',
            'nome.string' => 'O dado enviado não é válido',
            'nome.max' => 'O dado enviado ultrapassa o limite de 190 caracteres',
            'email.required' => 'Preencha o campo email',
            'email.email' => 'O dado enviado não é um email válido',
            'email.max' => 'O dado enviado ultrapassa o limite de 190 caracteres',
            'telefone.required' => 'Preencha o campo telefone',
            'telefone.celular_com_ddd' => 'O dado enviado não é um telefone válido',
            'cpf_cnpj.required' => 'Preencha o campo CPF',
            'cpf_cnpj.cpf' => 'O dado enviado não é um CPF válido',

            'cep.required' => 'Preencha o campo CEP',
            'cep.string' => 'Dado inválido',
            'uf.required' => 'Preencha o campo UF',
            'ufstring' => 'Dado inválido',
            'endereco.required' => 'Preencha o campo Estado',
            'endereco.string' => 'Dado inválido',
            'complemento.string' => 'Dado inválido',
            'bairro.string' => 'Dado inválido',
            'cidade.required' => 'Preencha o campo Cidade',
            'cidade.string' => 'Dado inválido',
      ];
    }
}
