<?php

namespace App\Actions;

use App\Mail\ConfirmacaoInscricaoAnalistaNotification;
use App\Mail\ConfirmacaoInscricaoInterlabNotification;
use App\Mail\NotifyInvalidEmail;
use App\Mail\NovoCadastroInterlabNotification;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\Mail;

class NotifyInscricaoInterlabAction
{
    public function execute(InterlabInscrito $inscrito, AgendaInterlab $interlab, $editingId = null)
    {
        if (! $editingId) {
            Mail::to('interlab@redemetrologica.com.br')
                ->cc(['tecnico@redemetrologica.com.br'])
                ->send(new NovoCadastroInterlabNotification($inscrito, $interlab));
        }

        // if $inscrito->pessoa->email is null or empty trown exception
        if (empty($inscrito->pessoa->email)) {
            Mail::to('sistema@redemetrologica.com.br')->send(new NotifyInvalidEmail($inscrito->pessoa, $interlab, auth()->user()));
        }

        Mail::to($inscrito->pessoa->email)
            ->send(new ConfirmacaoInscricaoInterlabNotification($inscrito, $interlab));

        if ($inscrito->analistas()->exists()) {
            foreach ($inscrito->analistas as $index => $analista) {
                Mail::to($analista->email)
                    ->send(new ConfirmacaoInscricaoAnalistaNotification($analista, $inscrito, $interlab));

                if ($interlab->status === 'CONFIRMADO' && ! empty($interlab->interlab->tag)) {
                    app(CriarEnviarSenhaInterlabAction::class)->execute($inscrito, $index * 15, $analista);
                }

            }
        } else {
            if ($interlab->status === 'CONFIRMADO' && ! empty($interlab->interlab->tag)) {
                app(CriarEnviarSenhaInterlabAction::class)->execute($inscrito, 15);
            }
        }
    }
}
