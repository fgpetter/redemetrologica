<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UpdateInscricaoCursoRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'valor' => formataMoeda($this->valor) ?? null,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['nullable', 'string', 'min:3'],
            'email' => ['nullable', 'email'],
            'telefone' => ['nullable', 'string'],
            'cpf' => ['required_without:empresa_id', 'nullable', 'cpf'],
            'cep' => ['required_without:empresa_id', 'nullable', 'string'],
            'uf' => ['required_without:empresa_id', 'nullable', 'string', 'max:2'],
            'cidade' => ['required_without:empresa_id', 'nullable', 'string'],
            'bairro' => ['required_without:empresa_id', 'nullable', 'string'],
            'endereco' => ['required_without:empresa_id', 'nullable', 'string'],
            'complemento' => ['nullable', 'string'],
            'valor' => ['nullable', 'string'],
            'certificado_emitido' => ['nullable', Rule::date()->format('Y-m-d')],
            'resposta_pesquisa' => ['nullable', Rule::date()->format('Y-m-d')],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.min' => 'O nome deve ter no mínimo 3 caracteres',
            'email.email' => 'O e-mail informado é inválido',
            'cpf.required_without' => 'CPF é obrigatório',
            'cpf.cpf' => 'O CPF informado é inválido',
            'cep.required_without' => 'CEP é obrigatório',
            'uf.required_without' => 'UF é obrigatório',
            'cidade.required_without' => 'Cidade é obrigatório',
            'bairro.required_without' => 'Bairro é obrigatório',
            'endereco.required_without' => 'Endereço é obrigatório',
            'certificado_emitido.date' => 'O campo Certificado Enviado não é uma data valida',
            'certificado_emitido.date_format' => 'O campo Certificado Enviado não é uma data valida',
            'resposta_pesquisa.date' => 'O o campo Pesqisa Respondida não é uma data valida',
            'resposta_pesquisa.date_format' => 'O o campo Pesqisa Respondida não é uma data valida',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user() ?? null,
            'request' => $this->all() ?? null,
            'uri' => request()->fullUrl() ?? null,
            'method' => get_class($this).'::'.__FUNCTION__,
            'errors' => $validator->errors() ?? null,
        ]);

        return back()->with('error', 'Houve um erro a processar os dados, tente novamente')
            ->withErrors($validator)
            ->withInput();
    }
}
