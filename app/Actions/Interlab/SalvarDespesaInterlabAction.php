<?php

namespace App\Actions\Interlab;

use App\Models\InterlabDespesaLancamento;
use Illuminate\Support\Facades\DB;

class SalvarDespesaInterlabAction
{
    public function __construct(private SyncFornecedorAvaliacaoAction $sincronizarAvaliacao) {}

    /**
     * @param  int  $agendaId  Agenda do lançamento
     * @param  int|null  $lancamentoId  Lançamento existente na edição
     * @param  array{fornecedorId: string, produtos: array, avaliacaoCusto: string, avaliacaoTempo: string, avaliacaoQualidade: string}  $entrada
     * @return InterlabDespesaLancamento Lançamento com os itens sincronizados
     */
    public function execute(int $agendaId, ?int $lancamentoId, array $entrada): InterlabDespesaLancamento
    {
        $dados = $entrada;

        return DB::transaction(function () use ($agendaId, $lancamentoId, $dados): InterlabDespesaLancamento {
            $fornecedorId = (int) $dados['fornecedorId'];

            if ($lancamentoId === null) {
                $lancamento = InterlabDespesaLancamento::query()->create([
                    'agenda_interlab_id' => $agendaId,
                    'fornecedor_id' => $fornecedorId,
                ]);
            } else {
                $lancamento = InterlabDespesaLancamento::query()
                    ->where('agenda_interlab_id', $agendaId)
                    ->lockForUpdate()
                    ->findOrFail($lancamentoId);

                $lancamento->update(['fornecedor_id' => $fornecedorId]);
            }

            $this->sincronizarItens($lancamento, array_values($dados['produtos']));

            $this->sincronizarAvaliacao->sync(
                $lancamento,
                $this->avaliacaoComoInt($dados['avaliacaoCusto']),
                $this->avaliacaoComoInt($dados['avaliacaoTempo']),
                $this->avaliacaoComoInt($dados['avaliacaoQualidade']),
            );

            return $lancamento;
        });
    }

    /**
     * @param  list<array<string, mixed>>  $produtos
     */
    private function sincronizarItens(InterlabDespesaLancamento $lancamento, array $produtos): void
    {
        $existentes = $lancamento->itens()->orderBy('id')->get();

        foreach ($produtos as $indice => $produto) {
            $quantidade = (float) ($produto['quantidade'] ?? 0);
            $valor = filled($produto['valor'] ?? null) ? (float) formataMoeda($produto['valor']) : null;

            $dadosItem = [
                'material_servico' => $produto['material_servico'],
                'fabricante' => $produto['fabricante'] ?? '',
                'cod_fabricante' => $produto['cod_fabricante'] ?? '',
                'quantidade' => $quantidade ?: null,
                'valor' => $valor,
                'total' => $valor !== null && $quantidade ? $valor * $quantidade : null,
                'lote' => $produto['lote'] ?? null,
                'validade' => filled($produto['validade'] ?? null) ? $produto['validade'] : null,
                'data_compra' => filled($produto['data_compra'] ?? null) ? $produto['data_compra'] : null,
            ];

            if ($existentes->has($indice)) {
                $existentes[$indice]->update($dadosItem);
            } else {
                $lancamento->itens()->create($dadosItem);
            }
        }

        $existentes->slice(count($produtos))->each->delete();
    }

    private function avaliacaoComoInt(?string $valor): ?int
    {
        return filled($valor) ? (int) $valor : null;
    }
}
