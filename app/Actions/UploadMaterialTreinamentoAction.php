<?php

namespace App\Actions;

use App\Models\AgendaCursoArquivo;
use App\Models\AgendaCursos;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class UploadMaterialTreinamentoAction
{
    public function execute(AgendaCursos $agendaCurso, UploadedFile $arquivo, ?string $descricao): AgendaCursoArquivo
    {
        $resumo = new ResumoExecucao('curso.upload_material', ['agenda_uid' => $agendaCurso->uid]);

        return $resumo->executar(function ($resumo) use ($agendaCurso, $arquivo, $descricao): AgendaCursoArquivo {
            $disk = Storage::disk('public');
            $directory = 'agendamento-curso-material/'.$agendaCurso->uid;
            $name = Str::slug(pathinfo($arquivo->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'material';
            $extension = strtolower($arquivo->getClientOriginalExtension());
            $baseName = $name.'_'.now()->timestamp;
            $filename = $baseName.'.'.$extension;
            $counter = 1;
            while ($disk->exists($directory.'/'.$filename)) {
                $filename = $baseName.'_'.$counter++.'.'.$extension;
            }
            $path = $resumo->etapa('armazenar', fn () => $arquivo->storeAs($directory, $filename, 'public'));
            if ($path === false) {
                throw new RuntimeException('Falha ao armazenar material de treinamento.');
            }

            try {
                return $resumo->etapa('persistir', fn () => DB::transaction(fn () => $agendaCurso->materiaisTreinamento()->create(['arquivo' => $path, 'descricao' => $descricao])));
            } catch (Throwable $e) {
                try {
                    if (! $disk->delete($path)) {
                        throw new RuntimeException('Falha ao compensar upload de material.');
                    }
                } catch (Throwable $cleanupError) {
                    report($cleanupError);
                    $resumo->incrementar('falhas_compensacao');
                    $resumo->contexto(['arquivo_uid' => hash('sha256', $path)]);
                }
                throw $e;
            }
        });
    }
}
