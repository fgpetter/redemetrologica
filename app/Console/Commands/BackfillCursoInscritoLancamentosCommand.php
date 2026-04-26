<?php

namespace App\Console\Commands;

use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BackfillCursoInscritoLancamentosCommand extends Command
{
    protected $signature = 'curso-inscritos:backfill-lancamentos {--dry-run : Apenas auditar e exibir o que seria feito, sem gravar}';

    protected $description = 'Vincula inscritos órfãos a lançamentos existentes e registra inconsistências que requerem intervenção manual';

    /** @var array<int, array{tipo: string, dados: array<string, mixed>}> */
    private array $inconsistencias = [];

    /** @var array<int, true> Lancamentos EFETIVADOS já registrados para evitar entradas duplicadas */
    private array $efetivadosRegistrados = [];

    private string $logPath;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->logPath = storage_path('logs/inconsistencias-cursos-lancamentos.log');

        // Auditoria de lançamentos duplicados — apenas leitura, independente de dry-run
        $this->auditarLancamentosDuplicados();

        if ($dryRun) {
            $this->warn('Modo --dry-run: vínculos serão revertidos ao final (transação de simulação).');
            try {
                DB::transaction(function (): void {
                    $this->vincularInscritosOrfaos();
                    throw new \RuntimeException('__rollback_dry_run__');
                });
            } catch (\RuntimeException $e) {
                if ($e->getMessage() !== '__rollback_dry_run__') {
                    throw $e;
                }
            }
            $this->info('Dry-run concluído (nada persistido).');
        } else {
            DB::transaction(function (): void {
                $this->vincularInscritosOrfaos();
            });
            $this->info('Vínculos aplicados com sucesso.');
        }

        $this->salvarLog();
        $this->info("Inconsistências registradas em: {$this->logPath}");

        return self::SUCCESS;
    }

    /**
     * Vincula cada inscrito órfão ao lançamento existente para sua pessoa de cobrança + agenda.
     *
     * Regras:
     * - Dentre órfãos com mesmo (pessoa_id, empresa_id, agenda_curso_id), processa apenas o
     *   de menor ID (canônico) — os demais são inconsistências e ficam no log para exclusão manual.
     * - Só reagrega valor/observações em lançamentos PROVISIONADOS; EFETIVADOS são preservados.
     * - Órfãos sem lançamento correspondente são registrados no log sem alteração.
     */
    private function vincularInscritosOrfaos(): void
    {
        $orfaos = CursoInscrito::query()
            ->whereNull('lancamento_financeiro_id')
            ->whereHas('agendaCurso', fn ($q) => $q->whereNull('empresa_id'))
            ->orderBy('id')
            ->get();

        // Agrupa por chave de unicidade; o primeiro de cada grupo é o canônico (menor id)
        $grupos = $orfaos->groupBy(
            fn (CursoInscrito $i): string => "{$i->pessoa_id}_{$i->empresa_id}_{$i->agenda_curso_id}"
        );

        foreach ($grupos as $grupo) {
            /** @var Collection<int, CursoInscrito> $grupo */
            $canonico = $grupo->first();

            // Registra duplicatas para exclusão manual — não toca nelas
            if ($grupo->count() > 1) {
                $this->registrarInscritosDuplicados($grupo);
            }

            $pessoaCobrancaId = $canonico->empresa_id ?? $canonico->pessoa_id;

            $lancamento = LancamentoFinanceiro::query()
                ->where('pessoa_id', $pessoaCobrancaId)
                ->where('agenda_curso_id', $canonico->agenda_curso_id)
                ->orderBy('id')
                ->first();

            if (! $lancamento) {
                $this->registrarOrfaoSemLancamento($canonico);

                continue;
            }

            $this->line("Inscrito id={$canonico->id} ({$canonico->nome}) → lançamento id={$lancamento->id} [{$lancamento->status}]");
            $canonico->update(['lancamento_financeiro_id' => $lancamento->id]);

            if ($lancamento->status === 'PROVISIONADO') {
                $this->reagregarLancamento($lancamento->id);
            } else {
                $this->registrarEfetivadoSemReagregacao($lancamento);
            }
        }
    }

    /**
     * Recalcula valor e observações apenas em lançamentos PROVISIONADOS.
     */
    private function reagregarLancamento(int $lancamentoId): void
    {
        $lancamento = LancamentoFinanceiro::query()->find($lancamentoId);
        if (! $lancamento || $lancamento->status !== 'PROVISIONADO') {
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

    // -------------------------------------------------------------------------
    // Auditoria — apenas leitura, sem alterações no banco
    // -------------------------------------------------------------------------

    /**
     * Detecta lançamentos financeiros duplicados por (pessoa_id, agenda_curso_id).
     * Nenhuma alteração é feita; resultado vai para o log.
     */
    private function auditarLancamentosDuplicados(): void
    {
        $grupos = LancamentoFinanceiro::query()
            ->select('lancamentos_financeiros.pessoa_id', 'lancamentos_financeiros.agenda_curso_id', DB::raw('COUNT(*) as c'), DB::raw('MIN(lancamentos_financeiros.id) as keep_id'))
            ->join('agenda_cursos', 'agenda_cursos.id', '=', 'lancamentos_financeiros.agenda_curso_id')
            ->whereNotNull('lancamentos_financeiros.agenda_curso_id')
            ->whereNull('agenda_cursos.empresa_id')
            ->groupBy('lancamentos_financeiros.pessoa_id', 'lancamentos_financeiros.agenda_curso_id')
            ->having('c', '>', 1)
            ->get();

        foreach ($grupos as $row) {
            $keepId = (int) $row->keep_id;

            $lancamentos = LancamentoFinanceiro::query()
                ->where('pessoa_id', $row->pessoa_id)
                ->where('agenda_curso_id', $row->agenda_curso_id)
                ->orderBy('id')
                ->get();

            $pessoa = Pessoa::query()->find($row->pessoa_id);
            $agenda = AgendaCursos::query()->with('curso')->find($row->agenda_curso_id);
            $idsExcluir = $lancamentos->where('id', '!=', $keepId)->pluck('id')->values()->toArray();

            $detalhes = $lancamentos->map(fn (LancamentoFinanceiro $l): array => [
                'id' => $l->id,
                'status' => $l->status,
                'valor' => 'R$ '.$l->valor,
                'data_emissao' => $l->data_emissao,
                'observacoes' => $l->observacoes
                    ? mb_substr($l->observacoes, 0, 100).(mb_strlen($l->observacoes) > 100 ? '...' : '')
                    : '(vazio)',
            ])->toArray();

            $this->adicionarInconsistencia('LANCAMENTOS_DUPLICADOS', [
                'descricao' => 'Múltiplos lançamentos financeiros para a mesma pessoa e agenda de curso.',
                'pessoa_id' => $row->pessoa_id,
                'pessoa_nome' => $pessoa?->nome_razao ?? '(não encontrada)',
                'agenda_curso_id' => $row->agenda_curso_id,
                'curso' => $agenda?->curso?->descricao ?? '(não encontrado)',
                'total' => (int) $row->c,
                'lancamentos' => $detalhes,
                'acao_sugerida' => "Manter ID {$keepId} (canônico). Verificar inscritos vinculados aos IDs a excluir e redirecionar se necessário.",
                'sql_sugerido' => 'UPDATE curso_inscritos SET lancamento_financeiro_id = '.$keepId.' WHERE lancamento_financeiro_id IN ('.implode(', ', $idsExcluir).');'
                    ."\nDELETE FROM lancamentos_financeiros WHERE id IN (".implode(', ', $idsExcluir).');',
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Registro de inconsistências específicas
    // -------------------------------------------------------------------------

    /** @param Collection<int, CursoInscrito> $grupo */
    private function registrarInscritosDuplicados(Collection $grupo): void
    {
        $canonico = $grupo->first();
        $idsExcluir = $grupo->skip(1)->pluck('id')->toArray();
        $agenda = AgendaCursos::query()->with('curso')->find($canonico->agenda_curso_id);
        $empresa = $canonico->empresa_id ? Pessoa::query()->find($canonico->empresa_id) : null;

        $this->adicionarInconsistencia('INSCRITOS_DUPLICADOS', [
            'descricao' => 'Múltiplos registros de inscrição sem lançamento para a mesma chave (pessoa_id, empresa_id, agenda_curso_id).',
            'pessoa_id' => $canonico->pessoa_id,
            'empresa_id' => $canonico->empresa_id ?? '(pessoa física)',
            'empresa_nome' => $empresa?->nome_razao ?? '(pessoa física)',
            'agenda_curso_id' => $canonico->agenda_curso_id,
            'curso' => $agenda?->curso?->descricao ?? '(não encontrado)',
            'nome_participante' => $canonico->nome ?? '(sem nome)',
            'total_duplicatas' => $grupo->count(),
            'id_canonico_mantido' => $canonico->id,
            'ids_para_excluir' => $idsExcluir,
            'acao_sugerida' => "Manter ID {$canonico->id} (vinculado automaticamente). Excluir os demais após confirmar ausência de dados únicos.",
            'sql_sugerido' => 'DELETE FROM curso_inscritos WHERE id IN ('.implode(', ', $idsExcluir).');',
        ]);
    }

    private function registrarOrfaoSemLancamento(CursoInscrito $inscrito): void
    {
        $pessoaCobrancaId = $inscrito->empresa_id ?? $inscrito->pessoa_id;
        $pessoa = Pessoa::query()->find($pessoaCobrancaId);
        $agenda = AgendaCursos::query()->with('curso')->find($inscrito->agenda_curso_id);

        $this->adicionarInconsistencia('INSCRITO_ORFAO_SEM_LANCAMENTO', [
            'descricao' => 'Inscrito sem lançamento financeiro correspondente. Não foi possível vincular automaticamente.',
            'inscrito_id' => $inscrito->id,
            'nome' => $inscrito->nome ?? '(sem nome)',
            'email' => $inscrito->email ?? '(sem email)',
            'valor_inscricao' => $inscrito->valor ? 'R$ '.$inscrito->valor : '(sem valor)',
            'data_inscricao' => $inscrito->data_inscricao
                ? Carbon::parse($inscrito->data_inscricao)->format('d/m/Y H:i')
                : '(sem data)',
            'tipo_cobranca' => $inscrito->empresa_id ? 'PJ' : 'PF',
            'pessoa_cobranca_id' => $pessoaCobrancaId,
            'pessoa_cobranca_nome' => $pessoa?->nome_razao ?? '(não encontrada)',
            'agenda_curso_id' => $inscrito->agenda_curso_id,
            'curso' => $agenda?->curso?->descricao ?? '(não encontrado)',
            'acao_necessaria' => 'Criar lançamento financeiro manualmente para esta inscrição, ou excluir o inscrito se for registro inválido.',
        ]);
    }

    private function registrarEfetivadoSemReagregacao(LancamentoFinanceiro $lancamento): void
    {
        // Cada lancamento só é registrado uma vez, mesmo que múltiplos órfãos sejam linkados a ele
        if (isset($this->efetivadosRegistrados[$lancamento->id])) {
            return;
        }

        $inscritos = CursoInscrito::query()
            ->where('lancamento_financeiro_id', $lancamento->id)
            ->get();

        $valorCalculado = $inscritos->sum('valor');
        $divergeValor = (float) $lancamento->valor !== (float) $valorCalculado;

        if (! $divergeValor) {
            return;
        }

        $this->efetivadosRegistrados[$lancamento->id] = true;

        $this->adicionarInconsistencia('LANCAMENTO_EFETIVADO_VALOR_DIVERGENTE', [
            'descricao' => 'Lançamento EFETIVADO com valor divergente da soma dos inscritos vinculados. Não foi recalculado automaticamente por segurança.',
            'lancamento_id' => $lancamento->id,
            'pessoa_id' => $lancamento->pessoa_id,
            'agenda_curso_id' => $lancamento->agenda_curso_id,
            'status' => $lancamento->status,
            'valor_atual_lancamento' => 'R$ '.$lancamento->valor,
            'valor_calculado_inscritos' => 'R$ '.$valorCalculado,
            'diferenca' => 'R$ '.((float) $valorCalculado - (float) $lancamento->valor),
            'inscritos_vinculados' => $inscritos->pluck('nome')->toArray(),
            'acao_necessaria' => 'Revisar manualmente se o valor do lançamento está correto, considerando possíveis descontos ou ajustes aplicados.',
            'sql_sugerido' => "UPDATE lancamentos_financeiros SET valor = {$valorCalculado} WHERE id = {$lancamento->id};",
        ]);
    }

    // -------------------------------------------------------------------------
    // Saída de log
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $dados */
    private function adicionarInconsistencia(string $tipo, array $dados): void
    {
        $this->inconsistencias[] = ['tipo' => $tipo, 'dados' => $dados];
    }

    private function salvarLog(): void
    {
        $agora = now()->format('d/m/Y H:i:s');
        $separador = str_repeat('═', 80);
        $linhas = [];

        $linhas[] = $separador;
        $linhas[] = '  RELATÓRIO DE INCONSISTÊNCIAS — CURSOS / LANÇAMENTOS FINANCEIROS';
        $linhas[] = "  Gerado em: {$agora}";
        $linhas[] = '  Total de inconsistências encontradas: '.count($this->inconsistencias);
        $linhas[] = $separador;
        $linhas[] = '';

        if (empty($this->inconsistencias)) {
            $linhas[] = '  Nenhuma inconsistência encontrada. Base de dados consistente.';
            $linhas[] = '';
        } else {
            $grupos = collect($this->inconsistencias)->groupBy('tipo');

            foreach ($grupos as $tipo => $itens) {
                $linhas[] = str_repeat('─', 80);
                $linhas[] = "  TIPO: {$tipo} ({$itens->count()} ocorrência(s))";
                $linhas[] = str_repeat('─', 80);
                $linhas[] = '';

                foreach ($itens->values() as $indice => $item) {
                    $num = $indice + 1;
                    $linhas[] = "  [{$num}] ".($item['dados']['descricao'] ?? '');
                    unset($item['dados']['descricao']);

                    foreach ($item['dados'] as $chave => $valor) {
                        if (is_array($valor)) {
                            $linhas[] = "      {$chave}:";
                            foreach ($valor as $subitem) {
                                $texto = is_array($subitem)
                                    ? json_encode($subitem, JSON_UNESCAPED_UNICODE)
                                    : (string) $subitem;
                                $linhas[] = "        - {$texto}";
                            }
                        } else {
                            $linhas[] = "      {$chave}: {$valor}";
                        }
                    }
                    $linhas[] = '';
                }
            }
        }

        $linhas[] = $separador;
        $linhas[] = '';

        file_put_contents($this->logPath, implode("\n", $linhas), FILE_APPEND);
    }
}
