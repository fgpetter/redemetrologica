<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ImportarAreasAvaliadoresLegadoCommand extends Command
{
    protected $signature = 'avaliadores:importar-areas-legado {--apply : Persiste os vínculos ausentes} {--snapshot= : Hash exibido pela simulação}';

    protected $description = 'Importa áreas de atuação de avaliadores do banco legado';

    public function handle(): int
    {
        try {
            $plano = $this->planejar();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $snapshot = $this->hash($plano);
        $this->table(
            ['Origem pessoa', 'Avaliador', 'Área', 'Data', 'Situação', 'Ação', 'Detalhes'],
            array_map(fn (array $item): array => [
                $item['origem_id'],
                $item['avaliador_id'] ?? '-',
                $item['area_id'] ?? '-',
                $item['data_cadastro'] ?? '-',
                $item['situacao'] ?? '-',
                $item['acao'],
                $item['detalhes'],
            ], $plano['itens']),
        );
        $this->line("Snapshot: {$snapshot}");
        $this->line('A importar: '.count(array_filter($plano['itens'], fn (array $item): bool => $item['acao'] === 'IMPORTAR')).'; preservados: '.count(array_filter($plano['itens'], fn (array $item): bool => $item['acao'] === 'PRESERVAR')).'; revisar: '.count(array_filter($plano['itens'], fn (array $item): bool => $item['acao'] === 'REVISAR')).'; extras no destino: '.count($plano['extras']).'.');
        $idsRevisao = array_column(array_filter($plano['itens'], fn (array $item): bool => $item['acao'] === 'REVISAR'), 'origem_id');
        if ($idsRevisao !== []) {
            $this->line('IDs de origem para revisão: '.implode(', ', $idsRevisao));
        }
        if ($plano['extras'] !== []) {
            $this->line('IDs extras no destino: '.implode(', ', $plano['extras']));
        }

        if (! $this->option('apply')) {
            $this->info('Simulação concluída; nenhuma alteração foi persistida.');

            return self::SUCCESS;
        }

        $snapshotInformado = $this->option('snapshot');
        if (! is_string($snapshotInformado) || preg_match('/^[a-f0-9]{64}$/', $snapshotInformado) !== 1) {
            $this->error('Snapshot ausente ou inválido. Rode novamente a simulação e informe o hash apresentado.');

            return self::FAILURE;
        }

        try {
            $idsInseridos = DB::transaction(function () use ($snapshotInformado): array {
                $planoAtual = $this->planejar();
                if (! hash_equals($snapshotInformado, $this->hash($planoAtual))) {
                    throw new RuntimeException('Os dados mudaram desde a simulação; nenhuma alteração foi aplicada.');
                }
                if ($planoAtual['extras'] !== []) {
                    throw new RuntimeException('Há vínculos no destino sem correspondência na origem; aplicação cancelada.');
                }

                $ids = [];
                foreach ($planoAtual['itens'] as $item) {
                    if ($item['acao'] !== 'IMPORTAR') {
                        continue;
                    }
                    $ids[] = DB::table('avaliador_areas')->insertGetId([
                        'uid' => uniqid(),
                        'avaliador_id' => $item['avaliador_id'],
                        'area_id' => $item['area_id'],
                        'data_cadastro' => $item['data_cadastro'],
                        'situacao' => $item['situacao'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return $ids;
            });
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->line('IDs inseridos: '.($idsInseridos === [] ? 'nenhum' : implode(', ', $idsInseridos)));
        $this->info('Importação concluída.');

        return self::SUCCESS;
    }

    /** @return array{itens: list<array{origem_id: int, cpf: string, avaliador_id: int|null, area_id: int|null, data_cadastro: string|null, situacao: string|null, acao: string, detalhes: string}>, extras: list<int>} */
    private function planejar(): array
    {
        $legado = $this->conexaoLegado();
        $schemaLegado = Schema::connection($legado->getName());
        foreach (['cad_pessoas', 'pessoas_tipo_pessoas', 'pessoas_areas_atuacao', 'areas_atuacao'] as $tabela) {
            if (! $schemaLegado->hasTable($tabela)) {
                throw new RuntimeException("Tabela legada ausente: {$tabela}.");
            }
        }

        $this->exigirColunas($schemaLegado->getColumnListing('cad_pessoas'), 'cad_pessoas', ['id_pessoa', 'cpf_cnpj']);
        $this->exigirColunas($schemaLegado->getColumnListing('pessoas_tipo_pessoas'), 'pessoas_tipo_pessoas', ['id', 'id_pessoa', 'id_tipo_pessoa']);
        $this->exigirColunas($schemaLegado->getColumnListing('pessoas_areas_atuacao'), 'pessoas_areas_atuacao', ['id', 'id_pessoa', 'id_area_atuacao', 'data_cad_area', 'id_situacao']);
        $this->exigirColunas($schemaLegado->getColumnListing('areas_atuacao'), 'areas_atuacao', ['id', 'descricao']);

        $fontes = $legado->table('pessoas_tipo_pessoas as ptp')
            ->join('pessoas_areas_atuacao as paa', 'paa.id_pessoa', '=', 'ptp.id_pessoa')
            ->leftJoin('cad_pessoas as cp', 'cp.id_pessoa', '=', 'ptp.id_pessoa')
            ->leftJoin('areas_atuacao as aa', 'aa.id', '=', 'paa.id_area_atuacao')
            ->where('ptp.id_tipo_pessoa', 6)
            ->select([
                'ptp.id_pessoa as origem_id', 'cp.cpf_cnpj', 'paa.id_area_atuacao as id_area', 'paa.data_cad_area as data_cadastro', 'paa.id_situacao as situacao',
                'aa.descricao as descricao_area',
            ])
            ->orderBy('ptp.id_pessoa')->orderBy('paa.id')
            ->get();

        $pessoasLegadasPorCpf = $legado->table('pessoas_tipo_pessoas as ptp')
            ->join('cad_pessoas as cp', 'cp.id_pessoa', '=', 'ptp.id_pessoa')
            ->where('ptp.id_tipo_pessoa', 6)
            ->distinct()->get(['ptp.id_pessoa', 'cp.cpf_cnpj'])
            ->groupBy(fn (object $pessoa): string => $this->digitos((string) $pessoa->cpf_cnpj));
        $pessoasPorCpf = DB::table('pessoas')->get(['id', 'cpf_cnpj', 'tipo_pessoa', 'deleted_at'])
            ->groupBy(fn (object $pessoa): string => $this->digitos((string) $pessoa->cpf_cnpj));
        $avaliadoresPorPessoa = DB::table('avaliadores')->get(['id', 'pessoa_id'])->groupBy('pessoa_id');
        $areasDestino = DB::table('areas_atuacao')->get(['id', 'descricao'])->keyBy('id');
        $destinos = DB::table('avaliador_areas')->get(['id', 'avaliador_id', 'area_id', 'data_cadastro', 'situacao']);
        $fontesChaveadas = [];
        $tuplasOrigemProcessadas = [];
        $itens = [];

        foreach ($fontes as $fonte) {
            $cpf = $this->digitos((string) $fonte->cpf_cnpj);
            $pessoas = $pessoasPorCpf->get($cpf, collect());
            $avaliadores = $pessoas->count() === 1 ? $avaliadoresPorPessoa->get($pessoas->first()->id, collect()) : collect();
            $avaliador = $avaliadores->count() === 1 ? $avaliadores->first() : null;
            $cpfOrigemDuplicado = $pessoasLegadasPorCpf->get($cpf, collect())->pluck('id_pessoa')->unique()->count() !== 1;
            $area = $areasDestino->get($fonte->id_area);
            $situacao = $this->situacao($fonte->situacao);
            $detalhes = 'Novo vínculo';
            $acao = 'IMPORTAR';

            if (! $this->cpfValido($cpf)) {
                $acao = 'REVISAR';
                $detalhes = 'CPF ausente ou inválido';
            } elseif ($cpfOrigemDuplicado) {
                $acao = 'REVISAR';
                $detalhes = 'CPF duplicado entre pessoas legadas avaliadoras';
            } elseif ($pessoas->count() !== 1) {
                $acao = 'REVISAR';
                $detalhes = 'Pessoa ausente ou CPF duplicado no destino';
            } elseif ($pessoas->first()->deleted_at !== null) {
                $acao = 'REVISAR';
                $detalhes = 'Pessoa correspondente excluída';
            } elseif ($pessoas->first()->tipo_pessoa !== 'PF') {
                $acao = 'REVISAR';
                $detalhes = 'Pessoa correspondente não é PF';
            } elseif ($avaliador === null) {
                $acao = 'REVISAR';
                $detalhes = $avaliadores->count() > 1 ? 'Mais de um avaliador vinculado à pessoa' : 'Avaliador correspondente ausente';
            } elseif ($area === null || (string) $area->descricao !== (string) $fonte->descricao_area) {
                $acao = 'REVISAR';
                $detalhes = 'Área ausente ou descrição divergente para o mesmo ID';
            } elseif (! $situacao['valida']) {
                $acao = 'REVISAR';
                $detalhes = 'Situação legada fora dos valores aceitos: 7, 8, 10 ou nulo';
            }

            $avaliadorId = $avaliador === null ? null : (int) $avaliador->id;
            $areaId = $area === null ? null : (int) $area->id;
            $data = $this->normalizarData($fonte->data_cadastro);
            if ($acao === 'IMPORTAR' && $data === false) {
                $acao = 'REVISAR';
                $detalhes = 'Data de cadastro inválida';
            }
            $data = $data === false ? null : $data;

            if ($acao === 'IMPORTAR') {
                $chave = $this->chave($avaliadorId, $areaId, $data, $situacao['valor']);
                $fontesChaveadas[$chave] = true;
                if (isset($tuplasOrigemProcessadas[$chave])) {
                    $acao = 'PRESERVAR';
                    $detalhes = 'Tupla repetida na origem';
                } else {
                    $tuplasOrigemProcessadas[$chave] = true;
                    $existente = $destinos->first(fn (object $destino): bool => $this->chave(
                        (int) $destino->avaliador_id,
                        (int) $destino->area_id,
                        $this->normalizarData($destino->data_cadastro) ?: null,
                        $destino->situacao,
                    ) === $chave);
                    if ($existente !== null) {
                        $acao = 'PRESERVAR';
                        $detalhes = 'Vínculo já existe';
                    }
                }
            }

            $itens[] = [
                'origem_id' => (int) $fonte->origem_id,
                'cpf' => $cpf,
                'avaliador_id' => $avaliadorId,
                'area_id' => $areaId,
                'data_cadastro' => $data,
                'situacao' => $situacao['valor'],
                'acao' => $acao,
                'detalhes' => $detalhes,
            ];
        }

        $extras = $destinos->filter(function (object $destino) use ($fontesChaveadas): bool {
            return ! isset($fontesChaveadas[$this->chave(
                (int) $destino->avaliador_id,
                (int) $destino->area_id,
                $this->normalizarData($destino->data_cadastro) ?: null,
                $destino->situacao,
            )]);
        })->pluck('id')->map(fn ($id): int => (int) $id)->values()->all();

        return ['itens' => $itens, 'extras' => $extras];
    }

    /** @param list<string> $colunas @param list<string> $necessarias */
    private function exigirColunas(array $colunas, string $tabela, array $necessarias): void
    {
        foreach ($necessarias as $coluna) {
            if (! in_array($coluna, $colunas, true)) {
                throw new RuntimeException("Coluna legada ausente: {$tabela}.{$coluna}.");
            }
        }
    }

    private function conexaoLegado(): ConnectionInterface
    {
        if (config('database.connections.legado')) {
            return DB::connection('legado');
        }
        $configuracao = config('database.connections.'.config('database.default'));
        $configuracao['database'] = 'legado_rede';
        config(['database.connections.legado' => $configuracao]);
        DB::purge('legado');

        return DB::connection('legado');
    }

    private function digitos(string $valor): string
    {
        return preg_replace('/\D+/', '', $valor) ?? '';
    }

    private function cpfValido(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($tamanho = 9; $tamanho < 11; $tamanho++) {
            $soma = 0;
            for ($indice = 0; $indice < $tamanho; $indice++) {
                $soma += (int) $cpf[$indice] * (($tamanho + 1) - $indice);
            }
            $digito = ($soma * 10) % 11;
            if ((int) $cpf[$tamanho] !== ($digito === 10 ? 0 : $digito)) {
                return false;
            }
        }

        return true;
    }

    /** @return array{valida: bool, valor: string|null} */
    private function situacao(mixed $situacao): array
    {
        if ($situacao === null || trim((string) $situacao) === '') {
            return ['valida' => true, 'valor' => null];
        }

        return match ((string) $situacao) {
            '7' => ['valida' => true, 'valor' => 'AVALIADOR EM TREINAMENTO'],
            '8' => ['valida' => true, 'valor' => 'AVALIADOR LIDER'],
            '10' => ['valida' => true, 'valor' => 'AVALIADOR'],
            default => ['valida' => false, 'valor' => null],
        };
    }

    private function normalizarData(mixed $data): string|false|null
    {
        if ($data === null || trim((string) $data) === '') {
            return null;
        }
        $data = date_create((string) $data);

        return $data === false ? false : $data->format('Y-m-d');
    }

    private function chave(int $avaliadorId, int $areaId, ?string $data, ?string $situacao): string
    {
        return json_encode([$avaliadorId, $areaId, $data, $situacao], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /** @param array{itens: list<array<string, mixed>>, extras: list<int>} $plano */
    private function hash(array $plano): string
    {
        return hash('sha256', json_encode($plano, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }
}
