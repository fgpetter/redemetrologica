<?php

namespace App\Console\Commands;

use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PreencherAnoCartasSenhaCommand extends Command
{
    protected $signature = 'interlab:preencher-ano-cartas-senha {--dry-run : Apenas simula as alterações}';

    protected $description = 'Preenche o ano de referência nas cartas-senha Interlab existentes';

    public function handle(): int
    {
        $disco = Storage::disk('public');
        $dryRun = (bool) $this->option('dry-run');
        $atualizadas = 0;
        $removidas = 0;
        $pendencias = 0;
        $pdfs = [];

        $cartas = DadosGeraDoc::query()->whereIn('tipo', DadosGeraDoc::TIPOS_CARTA_SENHA);

        foreach ($cartas->lazyById(100) as $carta) {
            try {
                if ($dryRun) {
                    $resultado = $this->processar($carta, $disco, true);
                } else {
                    $resultado = DB::transaction(function () use ($cartas, $carta, $disco): ?array {
                        $atual = (clone $cartas)->lockForUpdate()->find($carta->id);

                        return $atual === null ? null : $this->processar($atual, $disco, false);
                    });
                }
            } catch (QueryException $exception) {
                throw $exception;
            } catch (RuntimeException $exception) {
                $this->warn("Carta {$carta->id}: {$exception->getMessage()}");
                $pendencias++;

                continue;
            }

            if ($resultado !== null) {
                $atualizadas += (int) $resultado['alterou'];
                $removidas += (int) ($resultado['removida'] ?? false);
                if ($resultado['pdf'] !== null) {
                    $pdfs[$resultado['pdf']] = true;
                }
                if ($resultado['pendencia'] !== null) {
                    $this->warn("Carta {$carta->id}: {$resultado['pendencia']}");
                    $pendencias++;
                }
            }
        }

        $modo = $dryRun ? 'Simulação' : 'Concluído';
        $this->info("{$modo}. Cartas atualizadas: {$atualizadas}. Cartas removidas: {$removidas}. PDFs removidos: ".count($pdfs).". Pendências: {$pendencias}.");

        return $pendencias > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function precisaAtualizar(DadosGeraDoc $carta): bool
    {
        $salvo = $carta->getRawOriginal('file_name');

        return blank($carta->content['ano_referencia'] ?? null)
            || (filled($salvo) && $salvo !== $carta->storage_path);
    }

    /** @return array{alterou: bool, pdf: string|null, pendencia: string|null, removida?: bool}|null */
    private function processar(DadosGeraDoc $carta, FilesystemAdapter $disco, bool $dryRun): ?array
    {
        $content = $carta->content ?? [];
        $participanteId = $content['participante_id'] ?? null;
        $inscrito = is_numeric($participanteId)
            ? InterlabInscrito::with('agendaInterlab')->find($participanteId)
            : null;

        if ($inscrito === null) {
            $this->line("Carta {$carta->id}: ".($dryRun ? 'removeria' : 'removida').' (inscrição não encontrada).');
            if (! $dryRun) {
                $carta->delete();
            }

            return ['alterou' => false, 'pdf' => null, 'pendencia' => null, 'removida' => true];
        }

        if (! $this->precisaAtualizar($carta)) {
            return null;
        }

        $preencherAno = blank($content['ano_referencia'] ?? null);
        $pendencia = null;
        if ($preencherAno) {
            $agenda = $inscrito?->agendaInterlab;

            if ($preencherAno) {
                $pendencia = match (true) {
                    $inscrito === null => 'Inscrição não encontrada.',
                    $agenda === null => 'Agenda não encontrada.',
                    blank($agenda->ano_referencia) => 'Agenda sem ano de referência.',
                    default => null,
                };
                if ($pendencia === null) {
                    $content['ano_referencia'] = $agenda->ano_referencia;
                }
            }
        }

        $salvo = $carta->getRawOriginal('file_name');
        $caminho = DadosGeraDoc::normalizarCaminhoCartaSenha($salvo);
        $raiz = DadosGeraDoc::DIRETORIO_CARTAS_SENHA;
        if (filled($salvo) && ($caminho === null || ! in_array(dirname($caminho), [$raiz, $raiz.'/'.$carta->id], true))) {
            throw new RuntimeException('Caminho de PDF inválido ou pertencente a outra carta.');
        }

        $legado = $caminho !== null && $salvo !== $carta->storage_path;
        if ($pendencia !== null && ! $legado) {
            return ['alterou' => false, 'pdf' => null, 'pendencia' => $pendencia];
        }

        $pdf = $caminho !== null && $disco->fileExists($caminho) ? $caminho : null;
        if ($dryRun) {
            $mudanca = $preencherAno && $pendencia === null ? "preencher ano {$content['ano_referencia']}" : 'limpar referência legada';
            $remocao = $pdf === null ? '' : ' e remover PDF';
            $this->line("Carta {$carta->id}: {$mudanca}{$remocao}.");
        } else {
            if ($pdf !== null && ! $disco->delete($pdf)) {
                throw new RuntimeException('Falha ao remover o PDF.');
            }

            $carta->update(['content' => $content, 'file_name' => null]);
        }

        return ['alterou' => true, 'pdf' => $pdf, 'pendencia' => $pendencia];
    }
}
