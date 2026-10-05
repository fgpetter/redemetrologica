<?php

namespace App\Actions\Interlab;

use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Integrations\Correios\CorreiosLog;
use App\Models\InterlabLotePostagem;
use App\Models\InterlabLotePostagemItem;

class ProcessarInterlabLotePostagemAction
{
    private const JANELA_SEM_PROGRESSO_MINUTOS = 20;

    private const HANDOFF_ATRASO_SEGUNDOS = 5;

    public function __construct(
        private CriarPrePostagemItemLoteCorreiosAction $criarPrePostagem,
        private EmitirEtiquetasLotePostagemAction $emitirEtiquetas,
        private BaixarDaceLotePostagemAction $baixarDace,
        private EncerrarInterlabLotePostagemComErroAction $encerrar,
    ) {}

    public function execute(InterlabLotePostagem $lote, int $tentativa = 1): ResultadoEtapaLotePostagem
    {
        $lote->refresh();

        CorreiosLog::info('processar.inicio', [
            'lote_id' => $lote->id,
            'lote_uid' => $lote->uid,
            'status' => $lote->status->value,
            'tentativa' => $tentativa,
            'ultima_progressao_em' => $lote->ultima_progressao_em?->toIso8601String(),
        ]);

        if (in_array($lote->status, [InterlabLotePostagemStatus::Concluido, InterlabLotePostagemStatus::Erro], true)) {
            CorreiosLog::info('processar.ignorado', [
                'lote_id' => $lote->id,
                'status' => $lote->status->value,
            ]);

            return ResultadoEtapaLotePostagem::concluida();
        }

        if ($this->semProgresso($lote)) {
            CorreiosLog::warning('processar.sem_progresso', [
                'lote_id' => $lote->id,
                'ultima_progressao_em' => $lote->ultima_progressao_em?->toIso8601String(),
                'status' => $lote->status->value,
            ]);

            $this->encerrar->execute(
                $lote,
                $lote->status->value,
                'O processamento do lote não avançou nos últimos 20 minutos.',
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        if ($lote->status === InterlabLotePostagemStatus::ClassificandoServico) {
            if (! $this->todosClassificados($lote)) {
                CorreiosLog::info('processar.aguardando_classificacao', [
                    'lote_id' => $lote->id,
                ]);

                return ResultadoEtapaLotePostagem::concluida();
            }

            $this->avancarEtapa($lote, InterlabLotePostagemStatus::GerandoPrePostagens);
        }

        $lote->refresh();

        if ($lote->status === InterlabLotePostagemStatus::GerandoPrePostagens) {
            $resultado = $this->processarPrePostagens($lote, $tentativa);

            if (! $resultado->estaConcluida()) {
                return $resultado;
            }

            return $this->handoff($lote, InterlabLotePostagemStatus::GerandoEtiquetas);
        }

        if ($lote->status === InterlabLotePostagemStatus::GerandoEtiquetas) {
            $resultado = $this->emitirEtiquetas->execute($lote, $tentativa);

            if (! $resultado->estaConcluida()) {
                return $resultado;
            }

            return $this->handoff($lote, InterlabLotePostagemStatus::GerandoDeclaracao);
        }

        if ($lote->status === InterlabLotePostagemStatus::GerandoDeclaracao) {
            return $this->baixarDace->execute($lote);
        }

        return ResultadoEtapaLotePostagem::concluida();
    }

    private function processarPrePostagens(InterlabLotePostagem $lote, int $tentativa): ResultadoEtapaLotePostagem
    {
        $pendentes = $lote->itens()
            ->where('status', InterlabLotePostagemItemStatus::Classificado)
            ->orderBy('id')
            ->get();

        foreach ($pendentes as $item) {
            $resultado = $this->criarPrePostagem->execute($item, $tentativa);

            if ($resultado->estaFalhou()) {
                return $resultado;
            }

            if ($resultado->deveRetentar()) {
                return ResultadoEtapaLotePostagem::retentar(
                    max($resultado->atrasoSegundos, (int) (2 ** max(0, $tentativa - 1))),
                );
            }
        }

        $aindaPendentes = $lote->itens()
            ->where('status', InterlabLotePostagemItemStatus::Classificado)
            ->exists();

        if ($aindaPendentes) {
            return ResultadoEtapaLotePostagem::retentar(1);
        }

        return ResultadoEtapaLotePostagem::concluida();
    }

    private function handoff(InterlabLotePostagem $lote, InterlabLotePostagemStatus $status): ResultadoEtapaLotePostagem
    {
        $anterior = $lote->status->value;
        $this->avancarEtapa($lote, $status);

        CorreiosLog::info('processar.handoff', [
            'lote_id' => $lote->id,
            'de' => $anterior,
            'para' => $status->value,
            'atraso_segundos' => self::HANDOFF_ATRASO_SEGUNDOS,
        ]);

        return ResultadoEtapaLotePostagem::retentar(self::HANDOFF_ATRASO_SEGUNDOS);
    }

    private function avancarEtapa(InterlabLotePostagem $lote, InterlabLotePostagemStatus $status): void
    {
        $anterior = $lote->status->value;

        $lote->update([
            'status' => $status,
            'ultima_progressao_em' => now(),
        ]);

        CorreiosLog::info('processar.etapa', [
            'lote_id' => $lote->id,
            'de' => $anterior,
            'para' => $status->value,
        ]);
    }

    private function todosClassificados(InterlabLotePostagem $lote): bool
    {
        $itens = $lote->itens()->get();

        return $itens->isNotEmpty()
            && $itens->every(fn (InterlabLotePostagemItem $item): bool => $item->status === InterlabLotePostagemItemStatus::Classificado
                && filled($item->codigo_servico));
    }

    private function semProgresso(InterlabLotePostagem $lote): bool
    {
        if ($lote->ultima_progressao_em === null) {
            return false;
        }

        return $lote->ultima_progressao_em->lte(now()->subMinutes(self::JANELA_SEM_PROGRESSO_MINUTOS));
    }
}
