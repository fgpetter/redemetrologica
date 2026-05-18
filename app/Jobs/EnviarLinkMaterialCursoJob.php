<?php

namespace App\Jobs;

use App\Exceptions\InvalidEmailException;
use App\Mail\MaterialCursoNotification;
use App\Models\DadosGeraDoc;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class EnviarLinkMaterialCursoJob implements ShouldQueue
{
    use Queueable;

    public $dadosDocId;

    public $tries = 3;

    public function __construct($dadosDocId)
    {
        $this->dadosDocId = $dadosDocId;
    }

    public function handle(): void
    {
        try {
            $dadosDoc = DadosGeraDoc::findOrFail($this->dadosDocId);

            if (empty($dadosDoc->content['participante_email'])) {
                $content = [
                    'class' => self::class,
                    'dadosDoc_id' => $dadosDoc->id,
                ];
                new InvalidEmailException($content);
            } else {
                Mail::to($dadosDoc->content['participante_email'])
                    ->queue(new MaterialCursoNotification($dadosDoc));
            }
        } catch (\Exception $e) {
            report($e);
        }
    }
}
