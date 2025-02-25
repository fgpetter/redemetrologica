<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class AgendaCursoRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'investimento' => formataMoeda($this->investimento) ?? null,
            'investimento_associado' => formataMoeda($this->investimento_associado) ?? null,
            'valor_orcamento' => formataMoeda($this->valor_orcamento) ?? null,
            'destaque' => $this->destaque ?: 0,
            'site' => $this->site ?: 0,
            'inscricoes' => $this->inscricoes ?: 0,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['AGENDADO', 'CANCELADO', 'CONFIRMADO', 'REALIZADO', 'PROPOSTA ENVIADA', 'REAGENDAR'])],
            'destaque' => ['nullable', 'integer'],
            'tipo_agendamento' => ['required', Rule::in(['ONLINE', 'EVENTO', 'IN-COMPANY'])],
            'curso_id' => ['required', 'exists:cursos,id'],
            'instrutor_id' => ['required', 'exists:instrutores,id'],
            'empresa_id' => ['nullable', 'exists:pessoas,id'],
            'pessoa_id' => ['nullable', 'exists:pessoas,id'],
            'endereco_local' => ['nullable', 'string'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date'],
            'horario' => ['nullable', 'string'],
            'inscricoes' => ['nullable', 'integer'],
            'site' => ['nullable', 'integer'],
            'num_participantes' => ['nullable', 'integer'],
            'carga_horaria' => ['nullable', 'integer'],
            'investimento' => ['nullable', 'string'],
            'investimento_associado' => ['nullable', 'string'],
            'observacoes' => ['nullable', 'string'],
            'contato' => ['nullable', 'string'],
            'contato_email' => ['nullable', 'string'],
            'contato_telefone' => ['nullable', 'string'],
            'validade_proposta' => ['nullable', 'date'],
            'valor_orcamento' => ['nullable', 'string'],
            'status_proposta' => ['nullable', Rule::in(['PENDENTE', 'AGUARDANDO APROVACAO', 'APROVADA', 'REPROVADA'])],
            'material' => ['nullable', 'array'],
            'material.*' => ['integer', 'exists:curso_materiais,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Selecione uma opção válida',
            'status.in' => 'Selecione uma opção válida',
            'status_proposta.in' => 'Selecione uma opção válida',
            'destaque.integer' => 'O dado enviado não é valido',
            'tipo_agendamento.in' => 'Selecione uma opção válida',
            'curso_id.required' => 'É necessário escolher um curso',
            'curso_id.exists' => 'Selecione uma opção válida',
            'empresa_id.exists' => 'Selecione uma opção válida',
            'pessoa_id.exists' => 'Selecione uma opção válida',
            'instrutor_id.required' => 'É necessário escolher um instrutor',
            'instrutor_id.in' => 'Selecione uma opção válida',
            'endereco_local.string' => 'O dado enviado não é valido',
            'data_inicio.date' => 'O dado enviado não é uma data valida',
            'data_fim.date' => 'O dado enviado não é uma data valida',
            'validade_proposta.date' => 'O dado enviado não é uma data valida',
            'horario.string' => 'O dado enviado não é valido',
            'inscricoes.integer' => 'O dado enviado não é valido',
            'site.integer' => 'O dado enviado não é valido',
            'num_participantes.integer' => 'O dado enviado não é valido',
            'carga_horaria.integer' => 'O dado enviado não é valido',
            'investimento.string' => 'O dado enviado não é valido',
            'investimento_associado.string' => 'O dado enviado não é valido',
            'contato.string' => 'O dado enviado não é valido',
            'contato_email.string' => 'O dado enviado não é valido',
            'contato_telefone.string' => 'O dado enviado não é valido',
            'valor_orcamento.string' => 'O dado enviado não é valido',
            'observacoes.string' => 'O dado enviado não é valido',
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