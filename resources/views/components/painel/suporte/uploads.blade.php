@props(['arquivos'])
<div class="mt-3" x-data="suporteAnexos">
    <div class="row g-3 align-items-start">
    <div class="{{ isset($acoes) ? 'col-12 col-lg' : 'col-12' }}">
    <x-forms.input-field type="file" label="Adicionar anexos" name="uploads" x-on:change="enviar($event.target)" x-bind:disabled="enviando"
        multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx" />
    </div>
    @isset($acoes)<div class="col-12 col-lg-auto">{{ $acoes }}</div>@endisset
    </div>
    <small class="text-muted">Até 5 MB por arquivo, sem limite de quantidade. Você pode adicionar arquivos em várias seleções.</small>
    <div x-show="enviando" x-cloak class="text-primary small" role="status">Enviando arquivos…</div>
    <div x-text="erro" class="text-danger small" role="alert"></div>
    @error('uploads') <div class="text-danger">{{ $message }}</div> @enderror
    @error('uploads.*') <div class="text-danger">{{ $message }}</div> @enderror
    @error('upload') <div class="text-danger">{{ $message }}</div> @enderror
    <div class="d-flex flex-wrap gap-2 mt-2">
        @foreach($arquivos as $arquivo)
            <div class="border rounded px-2 py-1 d-flex align-items-center gap-2" wire:key="anexo-{{ $arquivo->uid }}">
                <a href="{{ route('suporte-arquivo-download', $arquivo->uid) }}"><i class="ri-attachment-line"></i> {{ $arquivo->nome }}</a>
                <button type="button" class="btn btn-sm btn-link text-danger p-0"
                    x-data x-on:click="Swal.fire({
                            title: 'Tem certeza?',
                            text: 'Remover este anexo da demanda ou atividade?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sim, remover!',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.removerAnexo('{{ $arquivo->uid }}');
                            }
                        })" aria-label="Remover {{ $arquivo->nome }}">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        @endforeach
    </div>
</div>
