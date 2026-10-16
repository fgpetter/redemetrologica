<div class="card" id="suporte-atividade-form">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ $atividadeUid ? 'Editar atividade' : ($parentUid ? 'Responder atividade' : 'Nova atividade') }}</h5>
        @if($atividadeUid || $parentUid)<button type="button" class="btn btn-sm btn-light" wire:click="novo">Cancelar</button>@endif
    </div>
    <form wire:submit="salvar" class="card-body">
        @if($parent)
            <blockquote class="border-start ps-3 mb-3">
                <strong>Respondendo a {{ $parent->owner?->name }}</strong>
                <div class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($parent->descricao), 180) }}</div>
            </blockquote>
        @endif
            <div wire:key="atividade-editor-{{ $rascunho }}">
            <x-forms.suporte-editor name="descricao" :value="$descricao" :rascunho="$rascunho" label="Descrição da atividade" />
        </div>
        <x-painel.suporte.uploads :arquivos="$arquivosSelecionados" />
        <div class="mt-4" x-data>
            <button class="btn btn-primary" wire:loading.attr="disabled" x-bind:disabled="$store.suporteUploads.total > 0">
                {{ $atividadeUid ? 'Salvar atividade' : 'Publicar atividade' }}
            </button>
        </div>
    </form>
</div>
@script
<script>
    $wire.on('suporte-erro', (event) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-right',
            iconColor: 'white',
            customClass: { popup: 'colored-toast' },
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            showCloseButton: true,
        });
        Toast.fire({ icon: 'error', title: event.message });
    });
    $wire.on('suporte-sucesso', (event) => Swal.fire({ icon: 'success', text: event.message, timer: 2000 }));
    $wire.on('suporte-responder', () => $wire.$el.scrollIntoView({ behavior: 'smooth', block: 'start' }));
    $wire.on('suporte-editar-atividade', () => $wire.$el.scrollIntoView({ behavior: 'smooth', block: 'start' }));
</script>
@endscript
