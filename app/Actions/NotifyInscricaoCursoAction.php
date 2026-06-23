<?php

namespace App\Actions;

use App\Exceptions\InvalidEmailException;
use App\Mail\ConfirmacaoInscricaoCursoNotification;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Mail;

class NotifyInscricaoCursoAction
{
    /**
     * Envia e-mail de confirmação de inscrição em curso para um ou mais participantes.
     *
     * @param  array<int, array<string, mixed>>  $participantes
     */
    public function execute(AgendaCursos $agenda, array $participantes, int $intervaloSegundos = 0): void
    {
        $delay = 0;

        foreach ($participantes as $participante) {
            $this->enviarParaParticipante($agenda, $participante, $delay, $intervaloSegundos);
            $delay += $intervaloSegundos;
        }
    }

    /**
     * Envia confirmação para um inscrito já persistido (fluxo admin).
     */
    public function executeParaInscrito(CursoInscrito $inscrito, AgendaCursos $agenda, ?Pessoa $empresa = null): void
    {
        $this->execute($agenda, [[
            'nome' => $inscrito->nome,
            'email' => $inscrito->email,
            'telefone' => $inscrito->telefone ?? '',
            'empresa_nome' => $empresa?->nome_razao,
            'inscrito_id' => $inscrito->id,
            'pessoa_id' => $inscrito->pessoa_id,
        ]]);
    }

    /**
     * @param  array<string, mixed>  $participante
     */
    private function enviarParaParticipante(AgendaCursos $agenda, array $participante, int $delay, int $intervaloSegundos): void
    {
        $email = strtolower(trim((string) ($participante['email'] ?? '')));

        if ($email === '') {
            $content = [
                'class' => self::class,
                'inscrito_id' => $participante['inscrito_id'] ?? null,
                'inscrito_pessoa_uid' => $participante['pessoa_id'] ?? '',
            ];
            new InvalidEmailException($content);

            return;
        }

        $dadosParticipante = [
            'nome' => $participante['nome'],
            'email' => $email,
            'telefone' => $participante['telefone'] ?? '',
            'empresa_nome' => $participante['empresa_nome'] ?? null,
        ];

        $mailable = new ConfirmacaoInscricaoCursoNotification($dadosParticipante, $agenda);

        if ($intervaloSegundos > 0) {
            Mail::to($email)->later(now()->addSeconds($delay), $mailable);
        } else {
            Mail::to($email)->queue($mailable);
        }
    }
}
