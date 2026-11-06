<?php

namespace App\Http\Requests;

use App\Helpers\Telefone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return auth()->user()->hasPermissionTo(['admin', 'funcionario']) || ($user->id == auth()->user()->id);
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('cpf_cnpj'))) {
            $this->merge(['cpf_cnpj' => return_only_nunbers($this->input('cpf_cnpj'))]);
        }
        $this->merge(Telefone::prepararCampos($this->only('telefone', 'telefone_alt', 'celular')));
        if (is_string($this->input('nome'))) {
            $this->merge(['nome' => ucwords(strtolower($this->input('nome')))]);
        }
        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower($this->input('email'))]);
        }

    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'unique:users,email,'.$this->route('user')->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'cpf_cnpj' => [Rule::requiredIf($this->route('user')->id == auth()->user()->id), 'cpf', 'unique:pessoas,cpf_cnpj,'.$this->route('user')->pessoa?->id],
            'telefone' => ['nullable', 'celular_com_ddd'],
            'telefone_alt' => ['nullable', 'celular_com_ddd'],
            'celular' => ['nullable', 'celular_com_ddd'],
            'cep' => ['nullable', 'string'],
            'endereco' => ['nullable', 'string'],
            'cidade' => ['nullable', 'string'],
            'uf' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Preencha o campo nome',
            'email.required' => 'Preencha o campo email',
            'email.email' => 'Não é um email válido',
            'email.unique' => 'Esse email já está em uso',
            'password.confirmed' => 'As senhas não conferem',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres',
            'telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
            'telefone_alt.celular_com_ddd' => 'Informe um telefone válido com DDD.',
            'celular.celular_com_ddd' => 'Informe um telefone válido com DDD.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user() ?? null,
            'request' => $this->except(['password', 'password_confirmation']),
            'uri' => $this->fullUrl(),
            'method' => $this->route()->getActionName(),
            'errors' => $validator->errors(),
        ]);
        throw new HttpResponseException(back()->withErrors($validator)->withInput($this->except(['password', 'password_confirmation']))->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente'));
    }
}
