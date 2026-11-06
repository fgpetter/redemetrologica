<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ImportarAvaliadoresLegadoCommand extends Command
{
    protected $signature = 'avaliadores:importar-legado {--apply : Persiste os novos vínculos} {--snapshot= : Hash exibido pela simulação}';

    protected $description = 'Importa avaliadores legados vinculando CPFs a pessoas existentes';

    /** @var list<string> */
    private const CAMPOS = [
        'curriculo',
        'data_ingresso',
        'situacao',
        'exp_min_comprovada',
        'curso_incerteza',
        'curso_iso',
        'curso_aud_interna',
        'parecer_psicologico',
    ];

    public function handle(): int
    {
        try {
            $dados = $this->planejar();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $snapshot = hash('sha256', json_encode($dados, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        $this->table(['Origem', 'Pessoa', 'Ação', 'Detalhes'], array_map(
            fn (array $item): array => [$item['origem_id'], $item['pessoa_id'] ?? '-', $item['acao'], $item['detalhes']],
            $dados['itens'],
        ));
        $this->line("Snapshot: {$snapshot}");
        $this->line('A importar: '.count(array_filter($dados['itens'], fn (array $item): bool => $item['acao'] === 'IMPORTAR')).'; revisão: '.count(array_filter($dados['itens'], fn (array $item): bool => $item['acao'] === 'REVISAR')).'; preservados: '.count(array_filter($dados['itens'], fn (array $item): bool => $item['acao'] === 'PRESERVAR')).'.');

        if (! $this->option('apply')) {
            $this->info('Simulação concluída; nenhuma alteração foi persistida.');

            return self::SUCCESS;
        }

        if (! is_string($this->option('snapshot')) || ! hash_equals($snapshot, $this->option('snapshot'))) {
            $this->error('Snapshot ausente ou diferente. Rode novamente a simulação e confirme o hash apresentado.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($snapshot): void {
                $atuais = $this->planejar();
                $hashAtual = hash('sha256', json_encode($atuais, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

                if (! hash_equals($snapshot, $hashAtual)) {
                    throw new RuntimeException('Os dados mudaram desde a simulação; nenhuma alteração foi aplicada.');
                }

                $idsInseridos = [];
                foreach ($atuais['itens'] as $item) {
                    if ($item['acao'] !== 'IMPORTAR') {
                        continue;
                    }

                    $id = DB::table('avaliadores')->insertGetId([
                        'uid' => uniqid(),
                        'pessoa_id' => $item['pessoa_id'],
                        ...$item['campos'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $idsInseridos[] = $id;
                }
                $this->line('IDs inseridos: '.($idsInseridos === [] ? 'nenhum' : implode(', ', $idsInseridos)));
                $this->info('Importados: '.count($idsInseridos).'; revisão: '.count(array_filter($atuais['itens'], fn (array $item): bool => $item['acao'] === 'REVISAR')).'; preservados: '.count(array_filter($atuais['itens'], fn (array $item): bool => $item['acao'] === 'PRESERVAR')).'.');
            });
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Importação concluída.');

        return self::SUCCESS;
    }

    /** @return array{itens: list<array{origem_id: int, cpf: string, pessoa_id: int|null, avaliador_id: int|null, acao: string, detalhes: string, campos?: array<string, mixed>}>} */
    private function planejar(): array
    {
        $legado = $this->conexaoLegado();

        foreach (['cad_pessoas', 'pessoas_tipo_pessoas'] as $table) {
            if (! Schema::connection($legado->getName())->hasTable($table)) {
                throw new RuntimeException("Tabela legada ausente: {$table}.");
            }
        }

        $colunas = Schema::connection($legado->getName())->getColumnListing('cad_pessoas');
        foreach (['id_pessoa', 'cpf_cnpj', ...self::CAMPOS] as $column) {
            if (! in_array($column, $colunas, true)) {
                throw new RuntimeException("Coluna legada ausente: cad_pessoas.{$column}.");
            }
        }

        $fontes = $legado->table('pessoas_tipo_pessoas as ptp')
            ->leftJoin('cad_pessoas as cp', 'cp.id_pessoa', '=', 'ptp.id_pessoa')
            ->where('ptp.id_tipo_pessoa', 6)
            ->select(['ptp.id_pessoa as origem_id', 'cp.id_pessoa', 'cp.cpf_cnpj', ...array_map(fn (string $column): string => "cp.{$column}", self::CAMPOS)])
            ->orderBy('ptp.id_pessoa')
            ->orderBy('ptp.id')
            ->get();

        $itens = [];
        $agrupados = $fontes->groupBy(fn (object $row): string => $this->digitos((string) $row->cpf_cnpj));
        $pessoasPorCpf = DB::table('pessoas')->get(['id', 'cpf_cnpj', 'tipo_pessoa', 'deleted_at'])
            ->groupBy(fn (object $pessoa): string => $this->digitos((string) $pessoa->cpf_cnpj));

        foreach ($fontes as $fonte) {
            $cpf = $this->digitos((string) $fonte->cpf_cnpj);
            $linhas = $agrupados->get($cpf);
            $cpfValido = strlen($cpf) === 11 && $this->cpfValido($cpf);
            $pessoas = $pessoasPorCpf->get($cpf, collect());

            if (! $cpfValido || $fonte->id_pessoa === null || $linhas->count() !== 1 || $pessoas->count() !== 1 || $pessoas->first()->tipo_pessoa !== 'PF' || $pessoas->first()->deleted_at !== null) {
                $itens[] = [
                    'origem_id' => $fonte->origem_id,
                    'cpf' => $cpf,
                    'pessoa_id' => null,
                    'avaliador_id' => null,
                    'acao' => 'REVISAR',
                    'detalhes' => $fonte->id_pessoa === null ? 'Cadastro legado ausente para o vínculo' : (! $cpfValido ? 'CPF inválido' : ($linhas->count() !== 1 ? 'CPF duplicado na origem' : ($pessoas->count() !== 1 ? 'Pessoa ausente ou CPF duplicado no destino' : ($pessoas->first()->tipo_pessoa !== 'PF' ? 'Pessoa correspondente não é PF' : 'Pessoa correspondente excluída')))),
                ];

                continue;
            }

            $pessoaId = (int) $pessoas->first()->id;
            $valores = [];
            $revisao = [];

            foreach (self::CAMPOS as $campo) {
                $valor = $fonte->{$campo};
                if ($campo === 'curriculo') {
                    $valores[$campo] = null;

                    continue;
                }
                if (in_array($campo, ['exp_min_comprovada', 'curso_incerteza', 'curso_iso', 'curso_aud_interna', 'parecer_psicologico'], true)) {
                    $normalizado = mb_strtoupper(trim((string) $valor));
                    if (! in_array($normalizado, ['', 'SIM', 'NAO', 'NÃO'], true)) {
                        $revisao[] = $campo;
                    }
                    $valor = $normalizado === 'SIM' ? 1 : 0;
                } elseif ($campo === 'situacao') {
                    $normalizado = mb_strtoupper(trim((string) $valor));
                    if (! in_array($normalizado, ['', '1', '2'], true)) {
                        $revisao[] = $campo;
                    }
                    $valor = match ($normalizado) {
                        '1' => 'ATIVO',
                        '2' => 'INATIVO',
                        default => null,
                    };
                } elseif ($campo === 'data_ingresso' && $valor !== null && trim((string) $valor) !== '') {
                    $data = date_create((string) $valor);
                    if ($data === false) {
                        $revisao[] = $campo;
                    } else {
                        $valor = $data->format('Y-m-d');
                    }
                }
                $valores[$campo] = $valor === '' ? null : $valor;
            }

            $existente = DB::table('avaliadores')->where('pessoa_id', $pessoaId)->first();
            if ($revisao !== []) {
                $acao = 'REVISAR';
                $detalhes = 'Valores inesperados: '.implode(', ', $revisao);
            } elseif ($existente !== null) {
                $diferencas = [];
                foreach ($valores as $campo => $valor) {
                    if ((string) ($existente->{$campo} ?? '') !== (string) ($valor ?? '')) {
                        $diferencas[] = $campo;
                    }
                }
                $acao = 'PRESERVAR';
                $detalhes = $diferencas === [] ? 'Avaliador já existe' : 'Avaliador já existe; divergências: '.implode(', ', $diferencas);
            } else {
                $acao = 'IMPORTAR';
                $detalhes = 'Novo vínculo';
            }

            $itens[] = [
                'origem_id' => $fonte->origem_id,
                'cpf' => $cpf,
                'pessoa_id' => $pessoaId,
                'avaliador_id' => $existente->id ?? null,
                'acao' => $acao,
                'detalhes' => $detalhes,
                'campos' => $valores,
            ];
        }

        return ['itens' => $itens];
    }

    private function conexaoLegado(): ConnectionInterface
    {
        if (config('database.connections.legado')) {
            return DB::connection('legado');
        }

        $config = config('database.connections.'.config('database.default'));
        $config['database'] = 'legado_rede';
        config(['database.connections.legado' => $config]);
        DB::purge('legado');

        return DB::connection('legado');
    }

    private function digitos(string $cpf): string
    {
        return preg_replace('/\D+/', '', $cpf) ?? '';
    }

    private function cpfValido(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($length = 9; $length < 11; $length++) {
            $sum = 0;
            for ($index = 0; $index < $length; $index++) {
                $sum += (int) $cpf[$index] * (($length + 1) - $index);
            }
            $digit = ($sum * 10) % 11;
            $digit = $digit === 10 ? 0 : $digit;
            if ((int) $cpf[$length] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
