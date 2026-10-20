<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SanitizarTelefonesCommand extends Command
{
    protected $signature = 'telefones:sanitizar {--dry-run : Apenas contabiliza os valores que seriam alterados}';

    protected $description = 'Remove caracteres não numéricos das colunas de telefone do banco de dados';

    /**
     * @var array<string, list<string>>
     */
    private array $colunasPorTabela = [
        'agenda_cursos' => ['contato_telefone'],
        'curso_inscritos' => ['telefone'],
        'fornecedores_areas' => ['pessoa_contato_telefone'],
        'interlab_analistas' => ['telefone'],
        'interlab_inscritos' => ['telefone'],
        'laboratorios' => ['telefone'],
        'laboratorios_internos' => ['telefone'],
        'notas_fiscais_avulsas' => ['telefone'],
        'pessoas' => ['telefone', 'telefone_alt', 'celular'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $totalRegistros = 0;
        $totalAlterados = 0;

        foreach ($this->colunasPorTabela as $tabela => $colunas) {
            if (! Schema::hasTable($tabela) || ! Schema::hasColumn($tabela, 'id')) {
                continue;
            }

            $colunas = array_values(array_filter(
                $colunas,
                fn (string $coluna): bool => Schema::hasColumn($tabela, $coluna),
            ));

            if ($colunas === []) {
                continue;
            }

            $registrosAnalisados = 0;
            $valoresAlterados = 0;

            DB::table($tabela)
                ->select(array_merge(['id'], $colunas))
                ->orderBy('id')
                ->chunkById(500, function ($registros) use ($tabela, $colunas, $dryRun, &$registrosAnalisados, &$valoresAlterados): void {
                    foreach ($registros as $registro) {
                        $registrosAnalisados++;
                        $alteracoes = [];

                        foreach ($colunas as $coluna) {
                            $telefone = $registro->{$coluna};

                            if ($telefone === null) {
                                continue;
                            }

                            $telefoneSanitizado = preg_replace('/\D/', '', (string) $telefone);

                            if ($telefoneSanitizado === $telefone) {
                                continue;
                            }

                            $alteracoes[$coluna] = $telefoneSanitizado;
                            $valoresAlterados++;
                        }

                        if ($alteracoes !== [] && ! $dryRun) {
                            DB::table($tabela)
                                ->where('id', $registro->id)
                                ->update($alteracoes);
                        }
                    }
                });

            $totalRegistros += $registrosAnalisados;
            $totalAlterados += $valoresAlterados;

            $this->line(sprintf(
                '%s: %d registros analisados, %d telefones %s.',
                $tabela,
                $registrosAnalisados,
                $valoresAlterados,
                $dryRun ? 'seriam sanitizados' : 'sanitizados',
            ));
        }

        $this->newLine();
        $this->info(sprintf(
            '%s: %d registros analisados e %d telefones %s.',
            $dryRun ? 'Simulação concluída' : 'Sanitização concluída',
            $totalRegistros,
            $totalAlterados,
            $dryRun ? 'seriam alterados' : 'alterados',
        ));

        return self::SUCCESS;
    }
}
