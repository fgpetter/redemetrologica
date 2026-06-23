<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StoreInscricaoCursoRequest extends FormRequest
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
            'agenda_curso_id' => ['required', 'exists:agenda_cursos,id'],
            'nome' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email'],
            'telefone' => ['nullable', 'string'],
            'tipo_inscricao' => ['required', 'in:cpf,cnpj'],
            'empresa_id' => ['required_if:tipo_inscricao,cnpj', 'nullable', 'exists:pessoas,id'],
            'cpf' => ['required_if:tipo_inscricao,cpf', 'nullable', 'cpf'],
            'cep' => ['required_if:tipo_inscricao,cpf', 'nullable', 'string'],
            'uf' => ['required_if:tipo_inscricao,cpf', 'nullable', 'string', 'max:2'],
            'cidade' => ['required_if:tipo_inscricao,cpf', 'nullable', 'string'],
            'bairro' => ['required_if:tipo_inscricao,cpf', 'nullable', 'string'],
            'endereco' => ['required_if:tipo_inscricao,cpf', 'nullable', 'string'],
            'complemento' => ['nullable', 'string'],
            'valor' => ['nullable', 'string'],
            'certificado_emitido' => ['nullable', Rule::date()->format('Y-m-d')],
            'resposta_pesquisa' => ['nullable', Rule::date()->format('Y-m-d')],
        ];
    }

    public function messages(): array
    {
        return [
            'agenda_curso_id.required' => 'O agendamento de cursos não foi encontrado',
            'agenda_curso_id.exists' => 'O agendamento de cursos não foi encontrado',
            'nome.required' => 'É obrigatório informar o nome do participante',
            'nome.min' => 'O nome deve ter no mínimo 3 caracteres',
            'email.required' => 'É obrigatório informar o e-mail do participante',
            'email.email' => 'O e-mail informado é inválido',
            'empresa_id.required_if' => 'É obrigatório selecionar uma empresa para inscrição CNPJ',
            'empresa_id.exists' => 'A empresa não foi encontrada',
            'cpf.required_if' => 'É obrigatório informar o CPF para inscrição CPF',
            'cpf.cpf' => 'O CPF informado é inválido',
            'cep.required_if' => 'CEP é obrigatório',
            'uf.required_if' => 'UF é obrigatório',
            'cidade.required_if' => 'Cidade é obrigatório',
            'bairro.required_if' => 'Bairro é obrigatório',
            'endereco.required_if' => 'Endereço é obrigatório',
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
