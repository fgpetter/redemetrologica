<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ConfirmaInscricaoInterlabRequest extends FormRequest
{
  protected function prepareForValidation()
  {
    $this->merge([
      'valor' => formataMoeda($this->valor) ?? null,
      'telefone' => return_only_nunbers($this->telefone)
    ]);
  }

  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      "pessoa_uid" => ['nullable', 'exists:pessoas,uid'],
      "empresa_uid" => ['required', 'exists:pessoas,uid'],
      "interlab_uid" => ['required', 'exists:agenda_interlabs,uid'],
      "encerra_cadastro" => ['nullable', 'integer', 'max:1', 'in:0,1'],
      "laboratorio" => ['required', 'string', 'max:191'],
      "responsavel_tecnico" => ['required', 'string', 'max:191'],
      "telefone" => ['nullable', 'string', 'min:10', 'max:11'],
      "email" => ['nullable', 'email', 'max:191'],
      "informacoes_inscricao" => ['nullable', 'string'],
      "cep" => ['required', 'string'],
      "endereco" => ['required', 'string'],
      "complemento" => ['nullable', 'string'],
      "bairro" => ['nullable', 'string'],
      "cidade" => ['nullable', 'string'],
      "uf" => ['required', 'string'],
      "valor" => ['nullable', 'string'],
    ];
  }

  public function messages(): array
  {
    return [
      'pessoa_uid.exists' => 'Essa pessaoa não existe na base de dados',
      'empresa_uid.required' => 'É necessário informar a empresa',
      'empresa_uid.exists' => 'Empresa não existe na base de dados',
      'laboratorio.required' => 'Preencha o campo laboratório',
      'laboratorio.max' => 'O campo laboratório deve ter no máximo :max caracteres',
      'responsavel_tecnico.required' => 'Preencha o campo laboratório',
      'responsavel_tecnico.max' => 'O campo laboratório deve ter no máximo :max caracteres',
      'telefone.*' => 'O telefone informado é inválido',
      'email.*' => 'O email informado é inválido',
      'cep.required' => 'Preencha o campo CEP',
      'endereco.required' => 'Preencha o campo endereço',
      'uf.required' => 'Preencha o campo UF',
      'valor.string' => 'O valor digitado é inválido',
    ];
  }

  public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
  {
    Log::channel('validation')->info("Erro de validação", [
        'user' => auth()->user() ?? null,
        'request' => $this->all() ?? null,
        'uri' => request()->fullUrl() ?? null,
        'method' => get_class($this) .'::'. __FUNCTION__,
        'errors' => $validator->errors() ?? null,
    ]);

    return back()->with('error', 'Houve um erro a processar os dados, tente novamente')
      ->withErrors($validator)
      ->withInput();
  }
} 