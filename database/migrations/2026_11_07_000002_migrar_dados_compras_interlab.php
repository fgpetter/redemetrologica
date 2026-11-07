<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('interlab_despesa_lancamentos')->orderBy('id')->chunkById(200, function ($lancamentos) {
                $itensPorLancamento = DB::table('interlab_despesas')
                    ->whereIn('interlab_despesa_lancamento_id', $lancamentos->pluck('id'))
                    ->whereNull('deleted_at')->get(['interlab_despesa_lancamento_id', 'data_compra'])
                    ->groupBy('interlab_despesa_lancamento_id');
                foreach ($lancamentos as $lancamento) {
                    $itens = $itensPorLancamento->get($lancamento->id, collect());
                    $datas = $itens->pluck('data_compra')->unique();
                    if ($itens->isNotEmpty() && $datas->count() === 1 && $datas->first() !== null) {
                        DB::table('interlab_despesa_lancamentos')->where('id', $lancamento->id)->update(['data_compra' => $datas->first()]);
                    }
                }
            });
            DB::table('fornecedores_avaliacao')->orderBy('id')->chunkById(200, function ($avaliacoes) {
                foreach ($avaliacoes as $avaliacao) {
                    $custo = $avaliacao->custo === null ? null : $avaliacao->custo * 2;
                    $tempo = $avaliacao->tempo === null ? null : $avaliacao->tempo * 2;
                    $qualidade = $avaliacao->qualidade === null ? null : $avaliacao->qualidade * 2;
                    DB::table('fornecedores_avaliacao')->where('id', $avaliacao->id)->update([
                        'custo' => $custo, 'tempo' => $tempo, 'qualidade' => $qualidade,
                        'media' => $custo !== null && $tempo !== null && $qualidade !== null ? round(($custo + $tempo + $qualidade) / 3, 2) : null,
                    ]);
                }
            });
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Migração irreversível: restaure o backup para recuperar as notas originais.');
    }
};
