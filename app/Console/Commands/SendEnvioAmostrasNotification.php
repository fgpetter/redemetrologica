<?php

namespace App\Console\Commands;

use App\Models\InterlabRodada;
use Illuminate\Console\Command;

class SendEnvioAmostrasNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-envio-amostras-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // search in interlab_rodadas table where data_envio_amostras_notification is not null and is less than or equal to today
        $rodadas = InterlabRodada::whereNotNull('data_envio_amostras_notification')
            ->where('data_envio_amostras', '<=', now())
            ->get();

        foreach ($rodadas as $rodada) {
            // adicionar na fila um mailable semelhante ao CertificadoInterlabMail com o conteúdo do email de envio de amostras
        }
        
    }
}
