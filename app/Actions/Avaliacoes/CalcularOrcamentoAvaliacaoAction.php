<?php

namespace App\Actions\Avaliacoes;

use App\Enums\ImpostoAvaliacao;
use App\Models\AgendaAvaliacao;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalcularOrcamentoAvaliacaoAction
{
    /**
     * Agrega as áreas e calcula os totais do orçamento em tempo de execução.
     *
     * @return array{
     *     data_inicio: ?string,
     *     data_fim: ?string,
     *     num_avaliadores: int,
     *     total_dias_trabalho: float,
     *     num_aval_treinamento: int,
     *     avaliacoes: string,
     *     num_ensaios: float,
     *     soma_avaliadores: float,
     *     soma_despesas_estimadas: float,
     *     soma_despesas_reais: float,
     *     perc_lucro: float,
     *     nf: float,
     *     valor_proposta: float,
     *     superavit: float,
     *     total_avaliadores: Collection<int, array{nome: string, total: float}>,
     *     total_geral_avaliadores: float
     * }
     */
    public function execute(AgendaAvaliacao $avaliacao, ?float $percLucro = null): array
    {
        $avaliacao->loadMissing(['areas.areaAtuacao', 'areas.avaliador.pessoa']);

        $areas = $avaliacao->areas;
        $percLucro ??= (float) ($avaliacao->perc_lucro ?? 0);

        $somaAvaliadores = (float) $areas->sum('valor_avaliador');
        $somaDespesasEstimadas = (float) $areas->sum('total_gastos_estim');
        $somaDespesasReais = (float) $areas->sum('total_gastos_reais');

        $nf = round(
            ($somaAvaliadores + $somaDespesasEstimadas) * ImpostoAvaliacao::Padrao->fator(),
            2
        );

        $valorProposta = round(
            $somaAvaliadores
            + ($somaAvaliadores * ($percLucro / 100))
            + $somaDespesasEstimadas
            + $nf,
            2
        );

        $superavit = round(
            $valorProposta - $somaAvaliadores - $somaDespesasReais - $nf,
            2
        );

        $totalAvaliadores = $areas
            ->groupBy(fn ($area) => optional($area->avaliador)->id)
            ->map(function ($areasDoAvaliador) {
                $avaliador = $areasDoAvaliador->first()->avaliador;

                return [
                    'nome' => optional($avaliador?->pessoa)->nome_razao ?? 'Não informado',
                    'total' => (float) $areasDoAvaliador->sum('valor_avaliador'),
                ];
            })
            ->values();

        return [
            'data_inicio' => $avaliacao->data_inicio
                ? Carbon::parse($avaliacao->data_inicio)->format('d/m/Y')
                : null,
            'data_fim' => $avaliacao->data_fim
                ? Carbon::parse($avaliacao->data_fim)->format('d/m/Y')
                : null,
            'num_avaliadores' => $areas->pluck('avaliador_id')->unique()->count(),
            'total_dias_trabalho' => (float) $areas->sum('dias'),
            'num_aval_treinamento' => $areas
                ->where('situacao', 'AVALIADOR EM TREINAMENTO')
                ->count(),
            'avaliacoes' => $areas
                ->map(fn ($area) => $area->areaAtuacao->descricao ?? 'Não informado')
                ->implode(', '),
            'num_ensaios' => (float) $areas->sum('num_ensaios'),
            'soma_avaliadores' => $somaAvaliadores,
            'soma_despesas_estimadas' => $somaDespesasEstimadas,
            'soma_despesas_reais' => $somaDespesasReais,
            'perc_lucro' => $percLucro,
            'nf' => $nf,
            'valor_proposta' => $valorProposta,
            'superavit' => $superavit,
            'total_avaliadores' => $totalAvaliadores,
            'total_geral_avaliadores' => (float) $totalAvaliadores->sum('total'),
        ];
    }
}
