<?php

namespace App\Console\Commands;

use App\Models\Convite;
use App\Mail\ConviteCurso;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviaConvites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:envia-convites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia emails de convites para os cursos e interlabs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $convite = Convite::whereNull('enviado')
        ->with('pessoa')
        ->with('agendaCurso')
        ->with('agendaInterlab')
        ->first();

        if( $convite ) {
            if( $convite->agendaCurso ){ Mail::to($convite->email)->send( new ConviteCurso( $convite ) ); };

            $convite->update([ 'enviado' => now(), 'status' => 'PENDENTE' ]);
            
            Log::channel('mailsent')->info("Convite enviado de \"{$convite->pessoa->email}\" para \"{$convite->email}\"", 
                [
                    'curso_id' => $convite->agendaCurso->id ?? null,
                    'interlab_id' => $convite->agendaInterlab->id ?? null,
                ]);
        }

    }
}
