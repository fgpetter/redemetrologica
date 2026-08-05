<?php

namespace App\Actions;

use App\Exceptions\InvalidEmailException;
use App\Mail\ConfirmacaoInscricaoAnalistaNotification;
use App\Mail\ConfirmacaoInscricaoInterlabNotification;
use App\Mail\NovoCadastroInterlabNotification;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\Mail;

class NotifyInscricaoInterlabAction
{
    public function execute(InterlabInscrito $inscrito, AgendaInterlab $interlab, mixed $editingId = null): void
    {
        $interlab->loadMissing('interlab');
        if (! $editingId) {
            Mail::to('interlab@redemetrologica.com.br')
                ->cc(['tecnico@redemetrologica.com.br'])
                ->send(new NovoCadastroInterlabNotification($inscrito, $interlab));
        }

        if (empty($inscrito->pessoa->email)) {
            $content = [
                'class' => self::class,
                'inscrito_id' => $inscrito->id,
                'inscrito_pessoa_uid' => $inscrito->pessoa?->id ?? '',
            ];
            new InvalidEmailException($content);
        } else {
            Mail::to($inscrito->pessoa->email)
                ->send(new ConfirmacaoInscricaoInterlabNotification($inscrito, $interlab));
        }

        if ($inscrito->analistas()->exists()) {
            foreach ($inscrito->analistas as $analista) {
                if (empty($analista->email)) {
                    $content = [
                        'class' => self::class,
                        'inscrito_id' => $inscrito->id,
                        'inscrito_pessoa_uid' => $inscrito->pessoa?->id ?? '',
                        'analista_id' => $analista->id,
                    ];
                    new InvalidEmailException($content);
                } else {
                    Mail::to($analista->email)
                        ->send(new ConfirmacaoInscricaoAnalistaNotification($analista, $inscrito, $interlab));
                }
            }
        }

        if (
            ! $editingId
            && $interlab->status === 'CONFIRMADO'
            && ! empty($interlab->interlab?->tag)
        ) {
            $this->dispararEnvioSenha($inscrito, $interlab);
        }
    }

    private function dispararEnvioSenha(InterlabInscrito $inscrito, AgendaInterlab $agenda): void
    {
        if (($agenda->interlab?->avaliacao ?? null) === 'ANALISTA') {
            $inscrito->loadMissing('analistas');

            foreach ($inscrito->analistas as $index => $analista) {
                app(CriarEnviarSenhaAnalistaAction::class)->execute(
                    $inscrito,
                    $analista,
                    ($index + 1) * 15,
                );
            }

            return;
        }

        app(CriarEnviarSenhaLaboratorioAction::class)->execute($inscrito, 15);
    }
}
