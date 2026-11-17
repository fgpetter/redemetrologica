<?php

namespace App\Actions;

use App\Helpers\ValorPorExtenso;
use App\Jobs\EnviarFr98Job;
use App\Models\AgendaCursos;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EnfileirarFr98Action
{
    public function execute(int $agendaCursoId): void
    {
        DB::transaction(function () use ($agendaCursoId): void {
            $agenda = AgendaCursos::query()
                ->with(['curso', 'instrutor.pessoa', 'despesas.materialPadrao'])
                ->lockForUpdate()->findOrFail($agendaCursoId);

            $horas = $agenda->despesas->first(fn ($despesa) => $despesa->materialPadrao?->descricao === 'HORAS-AULA');
            $email = trim((string) $agenda->instrutor?->pessoa?->email);
            $inCompany = $agenda->tipo_agendamento === 'IN-COMPANY';
            $participantes = $agenda->inscritos()->count();

            $validator = Validator::make([
                'status' => $agenda->status,
                'instrutor' => $agenda->instrutor?->pessoa?->getKey(),
                'email' => $email,
                'horas_quantidade' => $horas?->quantidade,
                'horas_valor' => $horas?->valor,
                'link_aula' => $agenda->link_aula,
                'data_inicio' => $agenda->data_inicio,
                'horario' => $agenda->horario,
                'participantes' => $participantes,
                'envio' => $agenda->fr98_envio_id,
            ], [
                'status' => $inCompany ? [] : ['required', 'in:CONFIRMADO'],
                'instrutor' => ['required'],
                'email' => ['required', 'email:rfc'],
                'horas_quantidade' => ['required', 'numeric', 'gt:0'],
                'horas_valor' => ['required', 'numeric', 'gt:0'],
                'link_aula' => ['required', 'url'],
                'data_inicio' => ['required', 'date'],
                'horario' => ['required', 'string'],
                'participantes' => $inCompany ? ['integer'] : ['integer', 'gt:0'],
                'envio' => ['prohibited'],
            ], [
                'status.*' => 'A agenda deve estar CONFIRMADA.',
                'instrutor.required' => 'Selecione um instrutor.',
                'email.*' => 'Corrija o e-mail no cadastro da pessoa vinculada ao instrutor.',
                'horas_quantidade.*' => 'Preencha a quantidade de HORAS-AULA com um valor maior que zero.',
                'horas_valor.*' => 'Preencha o valor unitário de HORAS-AULA com um valor maior que zero.',
                'link_aula.*' => 'Preencha um link da aula válido.',
                'data_inicio.*' => 'Preencha a data de início.',
                'horario.*' => 'Preencha o horário.',
                'participantes.*' => 'Cadastre pelo menos um participante.',
                'envio.prohibited' => 'Já existe um envio de FR98 pendente.',
            ]);

            if ($validator->fails()) {
                Log::channel('validation')->warning('Pré-requisitos para envio de FR98 não atendidos.', [
                    'agenda_curso_id' => $agenda->getKey(),
                    'campos' => $validator->errors()->keys(),
                ]);

                throw new ValidationException($validator);
            }

            $deslocamento = $agenda->despesas->first(fn ($despesa) => $despesa->materialPadrao?->descricao === 'DESLOCAMENTOS INSTRUTOR');
            $bonus = (int) round(($deslocamento?->quantidade ?? 0) * ($deslocamento?->valor ?? 0) * 100);
            $total = (int) round($horas->quantidade * $horas->valor * 100) + $bonus;
            $empresa = $inCompany ? Pessoa::find($agenda->empresa_id) : null;
            $data = $agenda->data_inicio->format('d/m/Y');

            if ($agenda->data_fim && ! $agenda->data_fim->isSameDay($agenda->data_inicio)) {
                $data .= ' a '.$agenda->data_fim->format('d/m/Y');
            }

            $snapshot = [
                'destinatario' => $email,
                'curso' => $agenda->curso->descricao,
                'tipo' => $inCompany ? 'In Company' : 'Aberto',
                'data' => $data,
                'horario' => $agenda->horario,
                'local' => $inCompany
                    ? implode(' — ', array_filter([$empresa?->nome_razao, $agenda->endereco_local])) ?: '—'
                    : 'Rede Metrológica RS',
                'contato' => $inCompany ? ($agenda->contato ?: '—') : '—',
                'telefone' => $inCompany ? ($agenda->contato_telefone ?: '—') : '—',
                'instrutor' => $agenda->instrutor->pessoa->nome_razao,
                'participantes' => $participantes,
                'valor_hora' => number_format((float) $horas->valor, 2, ',', '.'),
                'bonus' => number_format($bonus / 100, 2, ',', '.'),
                'total' => number_format($total / 100, 2, ',', '.'),
                'total_extenso' => ValorPorExtenso::reais($total),
                'link' => $agenda->link_aula,
            ];

            $tentativa = (string) Str::uuid();
            $agenda->update(['fr98_envio_id' => $tentativa]);

            Bus::dispatch((new EnviarFr98Job($agenda->getKey(), $tentativa, $snapshot))
                ->onConnection('database')->onQueue('default')->beforeCommit());

            Log::channel('mailsent')->info('FR98 enfileirado.', [
                'agenda_curso_id' => $agenda->getKey(),
                'tentativa_id' => $tentativa,
            ]);
        });
    }
}
