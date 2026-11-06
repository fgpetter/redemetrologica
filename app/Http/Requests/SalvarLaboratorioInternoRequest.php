<?php

namespace App\Http\Requests;

use App\Helpers\Telefone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SalvarLaboratorioInternoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(Telefone::prepararCampos($this->only('telefone')));
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'laboratorio_id' => ['required', 'numeric', 'exists:laboratorios,id'],
            'area_atuacao_id' => ['required', 'numeric', 'exists:areas_atuacao,id'],
            'nome' => ['nullable', 'string'],
            'cod_labinterno' => ['nullable', 'string'],
            'telefone' => ['nullable', 'celular_com_ddd'],
            'email' => ['nullable', 'email'],
            'responsavel_tecnico' => ['nullable', 'string'],
            'reconhecido' => ['required', 'numeric', 'in:1,0'],
            'sebrae' => ['required', 'numeric', 'in:1,0'],
            'site' => ['required', 'numeric', 'in:1,0'],
            'certificado' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'laboratorio_id.exists' => 'Selecione um laboratório',
            'area_atuacao_id.exists' => 'Selecione uma área válida',
            'nome.string' => 'O dado enviado não é válido',
            'cod_labinterno.string' => 'O dado enviado não é válido',
            'telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
            'email.email' => 'O dado enviado não é válido',
            'responsavel_tecnico.string' => 'O dado enviado não é válido',
            'reconhecido.in' => 'Selecione uma opção',
            'sebrae.in' => 'Selecione uma opção',
            'site.in' => 'Selecione uma opção',
            'certificado.file' => 'Arquivo inválido',
            'certificado.mimes' => 'O arquivo enviado não é do tipo PDF, DOC, DOCX',
            'certificado.max' => 'O arquivo é muito grande, tamanho máximo 2MB',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user(),
            'request' => $this->all(),
            'uri' => $this->fullUrl(),
            'method' => static::class,
            'errors' => $validator->errors(),
        ]);

        parent::failedValidation($validator);
    }
}
