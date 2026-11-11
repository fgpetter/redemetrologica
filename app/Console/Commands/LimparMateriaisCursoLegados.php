<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LimparMateriaisCursoLegados extends Command
{
    protected $signature = 'cursos:limpar-materiais-legados';

    protected $description = 'Remove arquivos e registros dos materiais legados de cursos';

    public function handle(): int
    {
        $materiais = Schema::hasTable('curso_materiais');
        $vinculos = Schema::hasTable('agenda_curso_materiais');
        $falhas = false;
        $root = public_path('curso-material');

        if ($materiais) {
            foreach (DB::table('curso_materiais')->orderBy('id')->get() as $material) {
                $nome = $material->arquivo;
                // O fluxo legado salvava apenas nomes de arquivos, nunca caminhos.
                if (! is_string($nome) || $nome === '' || $nome === '.' || $nome === '..'
                    || str_contains($nome, '/') || str_contains($nome, '\\') || str_contains($nome, "\0")
                    || is_link($root) || is_link($root.'/'.$nome)) {
                    $this->error("Material {$material->id}: caminho inseguro; referência preservada.");
                    $falhas = true;

                    continue;
                }

                $path = $root.'/'.$nome;
                try {
                    $public = public_path();
                    if (! is_dir($public) || ! is_readable($public) || ! is_executable($public)
                        || ($publicEntries = @scandir($public)) === false) {
                        throw new \RuntimeException('Não foi possível inspecionar o diretório público.');
                    }
                    $entries = [];
                    if (in_array('curso-material', $publicEntries, true)) {
                        if (is_link($root) || ! is_dir($root) || ! is_readable($root) || ! is_executable($root)
                            || ($entries = @scandir($root)) === false) {
                            throw new \RuntimeException('Não foi possível inspecionar o diretório de materiais.');
                        }
                    }
                    if (in_array($nome, $entries, true) && (is_link($path) || ! is_file($path) || ! File::delete($path))) {
                        throw new \RuntimeException('Não foi possível excluir o arquivo.');
                    }

                    DB::transaction(function () use ($vinculos, $material) {
                        if ($vinculos) {
                            DB::table('agenda_curso_materiais')->where('curso_material_id', $material->id)->delete();
                        }
                        DB::table('curso_materiais')->where('id', $material->id)->delete();
                    });
                } catch (Throwable $e) {
                    $this->error("Material {$material->id}: {$e->getMessage()}; referência preservada.");
                    $falhas = true;
                }
            }
        }

        if ($vinculos) {
            $query = DB::table('agenda_curso_materiais');
            if ($materiais) {
                $query->whereNotIn('curso_material_id', DB::table('curso_materiais')->select('id'));
            }
            $query->delete();
        }

        $this->info($falhas ? 'Limpeza incompleta; corrija as falhas e execute novamente.' : 'Limpeza legada concluída.');

        return $falhas ? self::FAILURE : self::SUCCESS;
    }
}
