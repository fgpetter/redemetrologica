<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AtualizarAgendaAvaliacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_inicio' => ['nullable', Rule::date()->format('Y-m-d')],
            'data_fim' => ['nullable', Rule::date()->format('Y-m-d')],
            'tipo_avaliacao_id' => ['nullable', 'numeric', 'exists:tipo_avaliacoes,id'],
            'laboratorio_interno_id' => ['nullable', 'numeric', 'exists:laboratorios_internos,id'],
            'status_proposta' => ['nullable', 'string', Rule::in(['PENDENTE', 'AGUARDANDO', 'APROVADA', 'REPROVADA'])],
            'fr_28' => ['nullable', 'numeric', 'in:0,1'],
            'fr_41' => ['nullable', 'numeric', 'in:0,1'],
            'fr_101' => ['nullable', 'numeric', 'in:0,1'],
            'fr_48' => ['nullable', 'numeric', 'in:0,1'],
            'relatorio_fr06' => ['nullable', 'string', Rule::in(['INCOMPLETA', 'COMPLETA', 'ENVIADA TODOS', 'ENVIADA AVALIADORES', 'ENVIADA LABORATORIO', 'NAO ENVIADA COMPLETA', 'APROVADA TODOS', 'APROVADA AVALIADORES', 'NAO APROVADA'])],
            'data_proc_laboratorio' => ['nullable', Rule::date()->format('Y-m-d')],
            'proc_laboratorio' => ['nullable', 'numeric', 'in:0,1'],
            'inf_avaliadores' => ['nullable', 'numeric', 'in:0,1'],
            'carta_reconhecimento' => ['nullable', 'numeric', 'in:0,1'],
            'retorno_fr06' => ['nullable', Rule::date()->format('Y-m-d')],
            'pesq_satisfacao' => ['nullable', Rule::date()->format('Y-m-d')],
            'data_proposta_acoes_corretivas' => ['nullable', Rule::date()->format('Y-m-d')],
            'data_acoes_corretivas' => ['nullable', Rule::date()->format('Y-m-d')],
            'acoes_aceitas' => ['nullable', 'string', 'in:NAO,SIM,PARCIALMENTE'],
            'data_reuniao_comite' => ['nullable', Rule::date()->format('Y-m-d')],
            'comite' => ['nullable', 'string', 'in:APROVADO,NAO APROVADO,COM PENDENCIAS'],
            'prazo_ajuste_pos_comite' => ['nullable', Rule::date()->format('Y-m-d')],
            'certificado' => ['nullable', 'numeric', 'in:0,1'],
            'validade_certificado' => ['nullable', Rule::date()->format('Y-m-d')],
            'enviado_certificado' => ['nullable', 'string', 'in:ENVIADO,NAO ENVIADO,PENDENTE'],
            'data_publicacao_site' => ['nullable', Rule::date()->format('Y-m-d')],
            'certificado_impresso' => ['nullable', 'numeric', 'in:0,1'],
            'ano_revisao_certificado' => ['nullable', Rule::date()->format('Y-m-d')],
            'obs' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_inicio.date' => 'Data de inicio inválida',
            'data_inicio.date_format' => 'Data de inicio inválida',
            'data_fim.date' => 'Data de fim inválida',
            'data_fim.date_format' => 'Data de fim inválida',
            'tipo_avaliacao_id.numeric' => 'Selecione uma opção válida',
            'tipo_avaliacao_id.exists' => 'Selecione uma opção válida',
            'fr_28.in' => 'Selecione uma opção válida',
            'fr_41.in' => 'Selecione uma opção válida',
            'fr_101.in' => 'Selecione uma opção válida',
            'fr_48.in' => 'Selecione uma opção válida',
            'relatorio_fr06.in' => 'Selecione uma opção válida',
            'laboratorio_interno_id.numeric' => 'Selecione uma opção válida',
            'laboratorio_interno_id.exists' => 'Selecione uma opção válida',
            'data_proc_laboratorio.date' => 'Data de processo de laboratório inválida',
            'data_proc_laboratorio.date_format' => 'Data de processo de laboratório inválida',
            'proc_laboratorio.in' => 'Selecione uma opção válida',
            'inf_avaliadores.in' => 'Selecione uma opção válida',
            'carta_reconhecimento.in' => 'Selecione uma opção válida',
            'retorno_fr06.date' => 'Data de retorno inválida',
            'retorno_fr06.date_format' => 'Data de retorno inválida',
            'pesq_satisfacao.date' => 'Data da pesquisa inválida',
            'pesq_satisfacao.date_format' => 'Data da pesquisa inválida',
            'data_proposta_acoes_corretivas.date' => 'Data da proposta de ações corretivas inválida',
            'data_proposta_acoes_corretivas.date_format' => 'Data da proposta de ações corretivas inválida',
            'data_acoes_corretivas.date' => 'Data das ações corretivas inválida',
            'data_acoes_corretivas.date_format' => 'Data das ações corretivas inválida',
            'acoes_aceitas.in' => 'Selecione uma opção válida',
            'data_reuniao_comite.date' => 'Data da reunião do comite inválida',
            'data_reuniao_comite.date_format' => 'Data da reunião do comite inválida',
            'comite.in' => 'Selecione uma opção válida',
            'prazo_ajuste_pos_comite.date' => 'Data do ajuste do prazo inválida',
            'prazo_ajuste_pos_comite.date_format' => 'Data do ajuste do prazo inválida',
            'data_publicacao_site.date' => 'Data da publicação no site inválida',
            'data_publicacao_site.date_format' => 'Data da publicação no site inválida',
            'certificado.in' => 'Selecione uma opção válida',
            'validade_certificado.date' => 'Data de validade do certificado inválida',
            'validade_certificado.date_format' => 'Data de validade do certificado inválida',
            'enviado_certificado.in' => 'Selecione uma opção válida',
            'certificado_impresso.in' => 'Selecione uma opção válida',
            'ano_revisao_certificado.date' => 'Data da revisão do certificado inválida',
            'ano_revisao_certificado.date_format' => 'Data da revisão do certificado inválida',
            'obs.string' => 'Conteúdo inválido',

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
