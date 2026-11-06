<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class EnderecoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pessoa_id' => ['required', 'integer'],
            'info' => ['nullable', 'string'],
            'cep' => ['required', 'string'],
            'endereco' => ['required', 'string'],
            'complemento' => ['nullable', 'string'],
            'cidade' => ['required', 'string'],
            'uf' => ['required', 'string'],
            'end_padrao' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'info.string' => 'Formato de texto inválido',
            'cep.required' => 'Preencha o campo CEP',
            'endereco.required' => 'Preencha o campo endereço',
            'cidade.required' => 'Preencha o campo cidade',
            'uf.required' => 'Preencha o uf',
            'end_padrao.integer' => 'Dado inválido',
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
        throw new HttpResponseException(back()->withErrors($validator, 'principal')->withInput()->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente'));
    }
}
