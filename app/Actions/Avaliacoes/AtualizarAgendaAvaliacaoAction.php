<?php

namespace App\Actions\Avaliacoes;

use App\Actions\Financeiro\GerarLancamentoAvaliacaoAction;
use App\Models\AgendaAvaliacao;
use App\Models\AreaAvaliada;
use App\Models\AvaliacaoAvaliador;
use App\Models\LancamentoFinanceiro;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AtualizarAgendaAvaliacaoAction
{
    public function __construct(
        private CalcularOrcamentoAvaliacaoAction $calcularOrcamento,
        private GerarLancamentoAvaliacaoAction $gerarLancamento,
        private EnviarPesquisaSatisfacaoAction $enviarPesquisa,
    ) {}

    /**
     * Atualiza a avaliação e aplica os efeitos da carta de reconhecimento.
     *
     * @param  AgendaAvaliacao  $avaliacao  Avaliação selecionada pela rota
     * @param  array<string, mixed>  $dados  Campos validados da atualização
     * @return array{bloqueio: string|null, pesquisaSemEmail: bool}
     */
    public function execute(AgendaAvaliacao $avaliacao, array $dados): array
    {
        $dados['validade_certificado'] ??= Carbon::parse($dados['data_fim'] ?? null)
            ->addYears(1)
            ->addMonths(3)
            ->toDateString();

        return DB::transaction(function () use ($avaliacao, $dados): array {
            $avaliacao = AgendaAvaliacao::query()->lockForUpdate()->findOrFail($avaliacao->getKey());
            $bloqueio = $this->motivoBloqueioDaCarta($avaliacao, $dados['carta_reconhecimento'] ?? null);

            if ($bloqueio !== null) {
                unset($dados['carta_reconhecimento']);
            }

            $avaliacao->update($dados);
            $pesquisaSemEmail = false;

            if ((int) $avaliacao->carta_reconhecimento === 1) {
                $this->registrarAvaliacoesDosAvaliadores($avaliacao);
                $this->gerarLancamento->execute($avaliacao);
                $pesquisaSemEmail = ! $this->enviarPesquisa->execute($avaliacao);
            }

            return ['bloqueio' => $bloqueio, 'pesquisaSemEmail' => $pesquisaSemEmail];
        });
    }

    private function motivoBloqueioDaCarta(AgendaAvaliacao $avaliacao, int|string|null $cartaSolicitada): ?string
    {
        if ($cartaSolicitada === null) {
            return null;
        }

        if ((int) $cartaSolicitada === 1) {
            $orcamento = $this->calcularOrcamento->execute($avaliacao);

            return (float) $orcamento['valor_proposta'] <= 0
                ? 'Defina um valor de proposta válido antes de marcar a Carta de Reconhecimento como SIM.'
                : null;
        }

        if ((int) $avaliacao->carta_reconhecimento === 1
            && LancamentoFinanceiro::where('agenda_avaliacao_id', $avaliacao->id)->exists()) {
            return 'Já há um lançamento financeiro para essa avaliação. Esse status só pode ser trocado se o lançamento for excluido.';
        }

        return null;
    }

    private function registrarAvaliacoesDosAvaliadores(AgendaAvaliacao $avaliacao): void
    {
        $areasAvaliadas = AreaAvaliada::where('avaliacao_id', $avaliacao->id)->get();

        foreach ($areasAvaliadas as $areaAvaliada) {
            AvaliacaoAvaliador::updateOrCreate([
                'agenda_avaliacao_id' => $avaliacao->id,
                'avaliador_id' => $areaAvaliada->avaliador_id,
                'empresa' => $avaliacao->laboratorio_id,
            ], [
                'data' => $avaliacao->data_inicio,
                'situacao' => $areaAvaliada->situacao,
                'inserido_por' => 'Inserido pelo sistema',
            ]);
        }
    }
}
