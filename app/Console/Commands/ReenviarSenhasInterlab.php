<?php

namespace App\Console\Commands;

use App\Jobs\ReenviarLinkSenhaInterlabJob;
use App\Models\AgendaInterlab;
use App\Models\DadosGeraDoc;
use Illuminate\Console\Command;

class ReenviarSenhasInterlab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reenviar-senhas-interlab {agenda_interlab_id : ID da agenda_interlabs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reenvia senha do interlab para todos os laboratorios inscritos na agenda informada';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $agendaInterlabId = (int) $this->argument('agenda_interlab_id');
        $agendaInterlab = AgendaInterlab::query()->find($agendaInterlabId);

        if (! $agendaInterlab) {
            $this->error('Agenda interlab nao encontrada.');

            return self::FAILURE;
        }

        $inscritos = $agendaInterlab->inscritos()
            ->with(['pessoa', 'laboratorio', 'empresa', 'agendaInterlab.interlab'])
            ->orderBy('id')
            ->get();

        if ($inscritos->isEmpty()) {
            $this->warn('Nenhum inscrito encontrado para a agenda informada.');

            return self::SUCCESS;
        }

        $enfileirados = 0;
        $ignorados = 0;

        foreach ($inscritos as $index => $inscrito) {
            $destinatario = $inscrito->pessoa?->email;
            $copia = $inscrito->email;

            if (blank($destinatario)) {
                $ignorados++;
                $this->warn("Inscrito {$inscrito->id} ignorado por falta de e-mail da pessoa.");

                continue;
            }

            $dadosDoc = DadosGeraDoc::create([
                'content' => [
                    'participante_id' => $inscrito->id,
                    'tag_senha' => $inscrito->tag_senha,
                    'informacoes_inscricao' => $inscrito->informacoes_inscricao,
                    'laboratorio_nome' => $inscrito->laboratorio->nome,
                    'laboratorio_email' => $inscrito->email,
                    'empresa_nome_razao' => $inscrito->empresa->nome_razao,
                    'empresa_cpf_cnpj' => $inscrito->empresa->cpf_cnpj,
                    'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
                ],
                'tipo' => 'tag_senha',
            ]);

            ReenviarLinkSenhaInterlabJob::dispatch(
                dadosDocId: $dadosDoc->id,
                emailDestinatario: $destinatario,
                emailCopia: filled($copia) ? $copia : null,
            )->delay(now()->addSeconds(($index + 1) * 30));

            $enfileirados++;
        }

        $this->info("Enfileirados: {$enfileirados}. Ignorados: {$ignorados}.");

        return self::SUCCESS;
    }
}
