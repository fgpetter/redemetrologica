<?php

namespace App\Actions\Interlab;

use App\Models\InterlabDespesaLancamento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalvarDespesaInterlabAction
{
    public function __construct(private SyncFornecedorAvaliacaoAction $sincronizarAvaliacao) {}

    /**
     * @param  int  $agendaId  Agenda do lançamento
     * @param  int|null  $lancamentoId  Lançamento existente na edição
     * @param  array{fornecedorId: string, dataCompra: ?string, tipo: ?string, especificacao: ?string, produtos: array, avaliacaoCusto: ?string, avaliacaoTempo: ?string, avaliacaoQualidade: ?string}  $entrada
     * @return InterlabDespesaLancamento Lançamento com os itens sincronizados
     */
    public function execute(int $agendaId, ?int $lancamentoId, array $entrada): InterlabDespesaLancamento
    {
        $dados = $entrada;

        return DB::transaction(function () use ($agendaId, $lancamentoId, $dados): InterlabDespesaLancamento {
            $fornecedorId = (int) $dados['fornecedorId'];
            $especificacao = Str::trim($dados['especificacao'] ?? '');
            $compra = [
                'fornecedor_id' => $fornecedorId,
                'data_compra' => $dados['dataCompra'] ?: null,
                'tipo' => $dados['tipo'] ?: null,
                'especificacao' => $especificacao === '' ? null : $especificacao,
            ];

            if ($lancamentoId === null) {
                if ($compra['especificacao'] === null) {
                    throw ValidationException::withMessages(['especificacao' => 'Informe a especificação.']);
                }
                $lancamento = InterlabDespesaLancamento::query()->create($compra + ['agenda_interlab_id' => $agendaId]);
            } else {
                $lancamento = InterlabDespesaLancamento::query()
                    ->where('agenda_interlab_id', $agendaId)
                    ->lockForUpdate()
                    ->findOrFail($lancamentoId);

                $erros = [];
                foreach (['data_compra' => ['dataCompra', 'a data da compra'], 'tipo' => ['tipo', 'o tipo'], 'especificacao' => ['especificacao', 'a especificação']] as $coluna => [$campo, $rotulo]) {
                    if (filled($lancamento->{$coluna}) && blank($compra[$coluna])) {
                        $erros[$campo] = "Informe {$rotulo}.";
                    }
                }
                if ($erros !== []) {
                    throw ValidationException::withMessages($erros);
                }
                $lancamento->update($compra);
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
        $existentes = $lancamento->itens()->get()->keyBy('id');
        $mantidos = [];

        foreach ($produtos as $produto) {
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
                'nota_fiscal' => $produto['nota_fiscal'] ?? null,
            ];

            if (! empty($produto['id'])) {
                $item = $existentes->get($produto['id']);
                abort_if(! $item || isset($mantidos[$item->id]), 404);
                $item->update($dadosItem);
            } else {
                $item = $lancamento->itens()->create($dadosItem);
            }
            $mantidos[$item->id] = true;
        }

        $existentes->except(array_keys($mantidos))->each->delete();
    }

    private function avaliacaoComoInt(?string $valor): ?int
    {
        return filled($valor) ? (int) $valor : null;
    }
}
