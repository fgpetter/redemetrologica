<?php

namespace App\Console\Commands;

use App\Actions\CriarEnviarSenhaInterlabAction;
use App\Actions\GerarTagSenhaInterlabAction;
use App\Models\AgendaInterlab;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use Illuminate\Console\Command;

class ReenviarSenhasInterlab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reenviar-senhas-interlab
                            {agenda_interlab_id : ID da agenda_interlabs}
                            {--resend-email : Reenfileira e-mails usando senhas existentes, sem gerar novas senhas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera e/ou reenvia senhas do interlab para inscritos (ou analistas) da agenda informada';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $agendaInterlabId = (int) $this->argument('agenda_interlab_id');
        $resendEmail = (bool) $this->option('resend-email');
        $agendaInterlab = AgendaInterlab::query()->find($agendaInterlabId);

        if (! $agendaInterlab) {
            $this->error('Agenda interlab nao encontrada.');

            return self::FAILURE;
        }

        $inscritos = $agendaInterlab->inscritos()
            ->with(['pessoa', 'laboratorio', 'empresa', 'agendaInterlab.interlab', 'analistas'])
            ->orderBy('id')
            ->get();

        if ($inscritos->isEmpty()) {
            $this->warn('Nenhum inscrito encontrado para a agenda informada.');

            return self::SUCCESS;
        }

        $processados = 0;
        $ignorados = 0;
        $semTagInterlab = 0;
        $delayIndex = 0;

        foreach ($inscritos as $inscrito) {
            $resultado = $this->processarInscrito($inscrito, $delayIndex, $resendEmail);
            $processados += $resultado['processados'];
            $ignorados += $resultado['ignorados'];
            $semTagInterlab += $resultado['sem_tag_interlab'];
        }

        $this->info("Enfileirados {$processados} envio(s). Ignorados {$ignorados}. Sem tag interlab {$semTagInterlab}.");

        return self::SUCCESS;
    }

    /**
     * @return array{processados: int, ignorados: int, sem_tag_interlab: int}
     */
    private function processarInscrito(InterlabInscrito $inscrito, int &$delayIndex, bool $resendEmail): array
    {
        $agendaInterlab = $inscrito->agendaInterlab;
        $interlab = $agendaInterlab?->interlab;

        if (empty($interlab?->tag)) {
            $this->warn("Inscrito {$inscrito->id} ignorado: interlab sem tag.");

            return ['processados' => 0, 'ignorados' => 0, 'sem_tag_interlab' => 1];
        }

        if (($interlab->avaliacao ?? null) === 'ANALISTA') {
            return $this->processarAnalistas($inscrito, $agendaInterlab, $delayIndex, $resendEmail);
        }

        if ($resendEmail) {
            if (empty($inscrito->tag_senha)) {
                $this->warn("Inscrito {$inscrito->id} ignorado: sem tag_senha para reenvio.");

                return ['processados' => 0, 'ignorados' => 1, 'sem_tag_interlab' => 0];
            }

            $delayIndex++;
            app(CriarEnviarSenhaInterlabAction::class)->execute(
                inscrito: $inscrito,
                delaySecs: $delayIndex * 30,
            );

            return ['processados' => 1, 'ignorados' => 0, 'sem_tag_interlab' => 0];
        }

        if (filled($inscrito->tag_senha) && $inscrito->senha_enviada !== null) {
            return ['processados' => 0, 'ignorados' => 1, 'sem_tag_interlab' => 0];
        }

        if (empty($inscrito->tag_senha)) {
            $tagSenha = app(GerarTagSenhaInterlabAction::class)->execute(
                $agendaInterlab,
                GerarTagSenhaInterlabAction::TIPO_LABORATORIO,
            );
            $inscrito->update(['tag_senha' => $tagSenha]);
            $inscrito->refresh();
        }

        $delayIndex++;
        app(CriarEnviarSenhaInterlabAction::class)->execute(
            inscrito: $inscrito,
            delaySecs: $delayIndex * 30,
        );

        return ['processados' => 1, 'ignorados' => 0, 'sem_tag_interlab' => 0];
    }

    /**
     * @return array{processados: int, ignorados: int, sem_tag_interlab: int}
     */
    private function processarAnalistas(
        InterlabInscrito $inscrito,
        AgendaInterlab $agendaInterlab,
        int &$delayIndex,
        bool $resendEmail,
    ): array
    {
        $analistas = $inscrito->analistas;

        if ($analistas->isEmpty()) {
            $this->warn("Inscrito {$inscrito->id} ignorado: nenhum analista.");

            return ['processados' => 0, 'ignorados' => 1, 'sem_tag_interlab' => 0];
        }

        $processados = 0;
        $ignorados = 0;

        foreach ($analistas as $analista) {
            if ($this->processarAnalista($inscrito, $agendaInterlab, $analista, $delayIndex, $resendEmail)) {
                $processados++;

                continue;
            }

            $ignorados++;
        }

        return ['processados' => $processados, 'ignorados' => $ignorados, 'sem_tag_interlab' => 0];
    }

    private function processarAnalista(
        InterlabInscrito $inscrito,
        AgendaInterlab $agendaInterlab,
        InterlabAnalista $analista,
        int &$delayIndex,
        bool $resendEmail,
    ): bool {
        if ($resendEmail && empty($analista->tag_senha)) {
            $this->warn("Analista {$analista->id} ignorado: sem tag_senha para reenvio.");

            return false;
        }

        if (empty($analista->tag_senha)) {
            $tagSenha = app(GerarTagSenhaInterlabAction::class)->execute(
                $agendaInterlab,
                GerarTagSenhaInterlabAction::TIPO_ANALISTA,
            );
            $analista->update(['tag_senha' => $tagSenha]);
            $analista->refresh();
        }

        $delayIndex++;
        app(CriarEnviarSenhaInterlabAction::class)->execute(
            inscrito: $inscrito,
            delaySecs: $delayIndex * 30,
            analista: $analista,
        );

        return true;
    }
}
