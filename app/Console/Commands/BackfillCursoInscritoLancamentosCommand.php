<?php

namespace App\Console\Commands;

use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BackfillCursoInscritoLancamentosCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'curso-inscritos:backfill-lancamentos
                            {--dry-run : Apenas auditar e exibir o que seria feito, sem gravar}';

    /**
     * @var string
     */
    protected $description = 'Deduplica lançamentos por cobrança+agenda, reaponta inscritos e preenche vínculos órfãos';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Modo --dry-run: alterações serão revertidas ao final (transação de simulação).');
            try {
                DB::transaction(function (): void {
                    $this->deduplicarLancamentos();
                    $this->vincularInscritosOrfaos();
                    throw new \RuntimeException('__rollback_dry_run__');
                });
            } catch (\RuntimeException $e) {
                if ($e->getMessage() !== '__rollback_dry_run__') {
                    throw $e;
                }
            }
            $this->info('Dry-run concluído (nada persistido).');

            return self::SUCCESS;
        }

        DB::transaction(function (): void {
            $this->deduplicarLancamentos();
            $this->vincularInscritosOrfaos();
        });
        $this->info('Backfill concluído com sucesso.');

        return self::SUCCESS;
    }

    /**
     * Mantém o lançamento de menor id por (pessoa_id, agenda_curso_id) e remove duplicados.
     */
    private function deduplicarLancamentos(): void
    {
        $duplicados = LancamentoFinanceiro::query()
            ->select('pessoa_id', 'agenda_curso_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as c'))
            ->groupBy('pessoa_id', 'agenda_curso_id')
            ->having('c', '>', 1)
            ->get();

        foreach ($duplicados as $row) {
            $canonicalId = (int) $row->keep_id;
            $outros = LancamentoFinanceiro::query()
                ->where('pessoa_id', $row->pessoa_id)
                ->where('agenda_curso_id', $row->agenda_curso_id)
                ->where('id', '!=', $canonicalId)
                ->orderBy('id')
                ->get();

            foreach ($outros as $dup) {
                $this->line("Duplicado lancamento id={$dup->id} → canônico id={$canonicalId}");

                CursoInscrito::query()
                    ->where('lancamento_financeiro_id', $dup->id)
                    ->update(['lancamento_financeiro_id' => $canonicalId]);

                $dup->delete();
            }

            $this->reagregarLancamentoCanonico($canonicalId);
        }
    }

    /**
     * Recalcula valor e observações no lançamento canônico a partir dos inscritos vinculados.
     */
    private function reagregarLancamentoCanonico(int $lancamentoId): void
    {
        $lancamento = LancamentoFinanceiro::query()->find($lancamentoId);
        if (! $lancamento) {
            return;
        }

        $inscritos = CursoInscrito::query()
            ->where('lancamento_financeiro_id', $lancamentoId)
            ->get();

        if ($inscritos->isEmpty()) {
            return;
        }

        $observacoes = '';
        foreach ($inscritos as $dado) {
            $data = Carbon::parse($dado->data_inscricao)->format('d/m/Y H:i');
            $observacoes .= "Inscrição de {$dado->nome}, com valor de R$ {$dado->valor}, em {$data} \n";
        }

        $lancamento->update([
            'valor' => $inscritos->sum('valor'),
            'observacoes' => $observacoes,
        ]);
    }

    /**
     * Preenche curso_inscritos.lancamento_financeiro_id quando nulo.
     */
    private function vincularInscritosOrfaos(): void
    {
        $orfaos = CursoInscrito::query()
            ->whereNull('lancamento_financeiro_id')
            ->get();

        foreach ($orfaos as $inscrito) {
            $agenda = AgendaCursos::query()->find($inscrito->agenda_curso_id);
            if (! $agenda) {
                $this->warn("Inscrito id={$inscrito->id}: agenda_curso_id={$inscrito->agenda_curso_id} inexistente — ignorado.");

                continue;
            }

            $pessoaCobrancaId = $inscrito->empresa_id ?? $inscrito->pessoa_id;
            $pessoaCobranca = Pessoa::query()->find($pessoaCobrancaId);
            if (! $pessoaCobranca) {
                $this->warn("Inscrito id={$inscrito->id}: pessoa de cobrança id={$pessoaCobrancaId} inexistente — manual.");

                continue;
            }

            $existente = LancamentoFinanceiro::query()
                ->where('pessoa_id', $pessoaCobrancaId)
                ->where('agenda_curso_id', $inscrito->agenda_curso_id)
                ->orderBy('id')
                ->first();

            if ($existente) {
                $this->line("Inscrito id={$inscrito->id} → vínculo ao lançamento id={$existente->id}");
                $inscrito->update(['lancamento_financeiro_id' => $existente->id]);
                $this->reagregarLancamentoCanonico($existente->id);

                continue;
            }

            $associado = (int) ($pessoaCobranca->associado ?? 0) === 1;
            $valorBase = $associado ? $agenda->investimento_associado : $agenda->investimento;
            $historico = 'Inscrição no curso - '.($agenda->curso?->descricao ?? '');

            $this->line("Inscrito id={$inscrito->id} → criar lançamento PROVISIONADO (cobrança pessoa_id={$pessoaCobrancaId})");
            $novo = LancamentoFinanceiro::create([
                'pessoa_id' => $pessoaCobrancaId,
                'agenda_curso_id' => $agenda->id,
                'historico' => $historico,
                'valor' => formataMoeda($valorBase),
                'centro_custo_id' => '3',
                'plano_conta_id' => '3',
                'data_emissao' => now(),
                'status' => 'PROVISIONADO',
                'observacoes' => 'Inscrição de '.$inscrito->nome.', em '.now()->format('d/m/Y H:i'),
            ]);
            $inscrito->update(['lancamento_financeiro_id' => $novo->id]);

            if ($inscrito->empresa_id) {
                $this->reagregarLancamentoCanonico($novo->id);
            }
        }
    }
}
