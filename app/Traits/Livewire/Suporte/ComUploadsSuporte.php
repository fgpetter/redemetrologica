<?php

namespace App\Traits\Livewire\Suporte;

use App\Actions\Suporte\ArquivosSuporteAction;
use App\Models\SuporteArquivo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

trait ComUploadsSuporte
{
    use WithFileUploads;

    #[Locked]
    public string $rascunho;

    public array $uploads = [];

    public array $anexos = [];

    public function updated(string $property): void
    {
        if ($property !== 'uploads') {
            return;
        }
        SuporteArquivo::autorizarRascunho($this->rascunho, auth()->user());
        Validator::make(['uploads' => $this->uploads], [
            'uploads' => ['array'],
            'uploads.*' => ArquivosSuporteAction::regras(),
        ], ['uploads.*.max' => 'Cada arquivo deve ter no máximo 5 MB.'])->validate();
        foreach ($this->uploads as $upload) {
            $arquivo = app(ArquivosSuporteAction::class)->enviar(auth()->user(), $upload, $this->rascunho);
            $this->anexos[] = $arquivo->uid;
        }
        $this->uploads = [];
    }

    public function removerAnexo(string $uid): void
    {
        SuporteArquivo::autorizarRascunho($this->rascunho, auth()->user());
        $this->anexos = array_values(array_diff($this->anexos, [$uid]));
    }

    /** @return Collection<int, SuporteArquivo> */
    protected function anexosSelecionados(): Collection
    {
        $contexto = SuporteArquivo::autorizarRascunho($this->rascunho, auth()->user());

        return SuporteArquivo::query()->where('tipo', 'anexo')->whereIn('uid', $this->anexos)
            ->where(function ($query) use ($contexto): void {
                $query->where(fn ($temporary) => $temporary->where('rascunho', $this->rascunho)->where('user_id', auth()->id()));
                if ($contexto['demanda_id'] && ! $contexto['nova_atividade']) {
                    $query->orWhere(fn ($saved) => $saved->where('demanda_id', $contexto['demanda_id'])->where('atividade_id', $contexto['atividade_id']));
                }
            })->get();
    }
}
