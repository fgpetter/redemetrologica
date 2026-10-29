<?php

namespace App\Actions\Suporte;

use App\Models\SuporteArquivo;
use App\Models\SuporteAtividade;
use App\Models\SuporteDemanda;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;

class ArquivosSuporteAction
{
    public const Extensoes = 'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx';

    /** @return array<int, string> */
    public static function regras(bool $imagem = false): array
    {
        $extensoes = $imagem ? 'jpg,jpeg,png,gif,webp' : self::Extensoes;

        return ['required', 'file', 'max:5120', 'mimes:'.$extensoes, 'extensions:'.$extensoes];
    }

    public function enviar(User $user, UploadedFile $upload, string $rascunho, bool $imagem = false): SuporteArquivo
    {
        SuporteArquivo::autorizarRascunho($rascunho, $user);
        Validator::make(['upload' => $upload], ['upload' => self::regras($imagem)], [
            'upload.max' => 'Cada arquivo deve ter no máximo 5 MB.',
            'upload.mimes' => 'Formato de arquivo não permitido.',
            'upload.extensions' => 'Extensão de arquivo não permitida.',
        ])->validate();

        $nome = mb_substr(basename($upload->getClientOriginalName()), 0, 255);
        $mime = $upload->getMimeType();
        $tamanho = $upload->getSize();
        $caminho = $upload->store('suporte/arquivos', 'local');
        if (! $caminho) {
            throw new RuntimeException('Não foi possível armazenar o arquivo.');
        }

        try {
            $arquivo = SuporteArquivo::query()->create([
                'uid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'rascunho' => $rascunho,
                'tipo' => $imagem ? 'imagem' : 'anexo',
                'nome' => $nome,
                'caminho' => $caminho,
                'mime' => $mime,
                'tamanho' => $tamanho,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($caminho);
            throw $exception;
        }

        if ($upload instanceof TemporaryUploadedFile) {
            $upload->delete();
        }

        return $arquivo;
    }

    /** @return Collection<int, SuporteArquivo> */
    public function disponiveis(User $user, string $rascunho, ?SuporteDemanda $demanda, ?SuporteAtividade $atividade = null, bool $novaAtividade = false): Collection
    {
        $contexto = SuporteArquivo::autorizarRascunho($rascunho, $user);
        abort_unless($contexto['demanda_id'] === $demanda?->id && $contexto['atividade_id'] === $atividade?->id && $contexto['nova_atividade'] === $novaAtividade, 404);

        $temporarios = SuporteArquivo::query()->where('rascunho', $rascunho)->where('user_id', $user->id)->lockForUpdate()->get();
        if (! $demanda || $novaAtividade) {
            return $temporarios;
        }

        $existentes = SuporteArquivo::query()->where('demanda_id', $demanda->id)
            ->where('atividade_id', $atividade?->id)->lockForUpdate()->get();

        return $existentes->concat($temporarios);
    }

    /**
     * @param  Collection<int, SuporteArquivo>  $disponiveis
     * @param  array<int, string>  $anexos
     * @param  array<int, string>  $imagens
     */
    public function vincular(Collection $disponiveis, array $anexos, array $imagens, SuporteDemanda $demanda, ?SuporteAtividade $atividade = null): void
    {
        foreach ($anexos as $uid) {
            if (! $disponiveis->contains(fn (SuporteArquivo $arquivo) => $arquivo->uid === $uid && $arquivo->tipo === 'anexo')) {
                throw ValidationException::withMessages(['suporte' => 'Um dos anexos não pertence a este formulário.']);
            }
        }
        $usados = array_merge($anexos, $imagens);
        foreach ($disponiveis as $arquivo) {
            if (in_array($arquivo->uid, $usados, true)) {
                $arquivo->update(['demanda_id' => $demanda->id, 'atividade_id' => $atividade?->id, 'rascunho' => null]);

                continue;
            }
            $arquivo->delete();
        }
    }
}
