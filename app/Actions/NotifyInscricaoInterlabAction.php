<?php

namespace App\Actions;

use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\Mail;
use App\Mail\NovoCadastroInterlabNotification;
use App\Actions\CriarEnviarSenhaAnalistaAction;
use App\Mail\ConfirmacaoInscricaoAnalistaNotification;
use App\Mail\ConfirmacaoInscricaoInterlabNotification;

class NotifyInscricaoInterlabAction
{
    public function execute(InterlabInscrito $inscrito, AgendaInterlab $interlab, $editingId = null)
    {
      if (! $editingId) {
        Mail::to('interlab@redemetrologica.com.br')
        ->cc(['tecnico@redemetrologica.com.br'])
        ->send(new NovoCadastroInterlabNotification($inscrito, $interlab));
      }
      
      Mail::to($inscrito->pessoa->email)
        ->send(new ConfirmacaoInscricaoInterlabNotification($inscrito, $interlab));

      if ($inscrito->analistas?->count() > 0) {
        foreach ($inscrito->analistas as $analista) {
          Mail::to($analista->email)
            ->send(new ConfirmacaoInscricaoAnalistaNotification($analista, $inscrito, $interlab));

            if ($interlab->status === 'CONFIRMADO' && !empty($interlab->interlab->tag)) {
              app(CriarEnviarSenhaAnalistaAction::class)->execute($inscrito, $analista, 1);
            }

        }
      } else {
        if ($interlab->status === 'CONFIRMADO' && !empty($interlab->interlab->tag)) {
          app(CriarEnviarSenhaAction::class)->execute($inscrito, 1);
        }
      }
    }

}
