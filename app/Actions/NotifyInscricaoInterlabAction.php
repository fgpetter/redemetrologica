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
    public function execute(InterlabInscrito $inscrito, AgendaInterlab $interlab, $editingId = null)
    {
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
            foreach ($inscrito->analistas as $index => $analista) {
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
    }
}
