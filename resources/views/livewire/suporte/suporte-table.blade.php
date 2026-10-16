<div>
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0">Suporte</h5>
            <a href="{{ route('suporte-insert') }}" class="btn btn-primary"><i class="ri-add-line"></i> Nova demanda</a>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <x-forms.input-field label="Buscar por título" name="busca" wire:model.live.debounce.300ms="busca" placeholder="Buscar demanda…" />
                </div>
                <div class="col-md-4">
                    <x-forms.input-select label="Status" name="status" wire:model.live="status">
                        <option value="ativas">Todas demandas</option>
                        <option value="todos">Todos os status</option>
                        @foreach($statuses as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </x-forms.input-select>
                </div>
            </div>
            @error('suporte') <div class="alert alert-danger">{{ $message }}</div> @enderror
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr><th style="width: 90px">Prioridade</th><th>Demanda</th><th>Autor</th><th>Status</th><th>Atividades</th><th>Classificação</th><th>Horas</th></tr>
                    </thead>
                    <tbody>
                        @forelse($demandas as $demanda)
                            <tr wire:key="demanda-{{ $demanda->uid }}">
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-light" aria-label="Subir demanda"
                                            wire:click="mover('{{ $demanda->uid }}', 'subir')" wire:loading.attr="disabled" wire:target="mover"
                                            @disabled($demanda->prioridade == $primeiraPrioridade)><i class="ri-arrow-up-s-line"></i></button>
                                        <button type="button" class="btn btn-sm btn-light" aria-label="Descer demanda"
                                            wire:click="mover('{{ $demanda->uid }}', 'descer')" wire:loading.attr="disabled" wire:target="mover"
                                            @disabled($demanda->prioridade == $ultimaPrioridade)><i class="ri-arrow-down-s-line"></i></button>
                                    </div>
                                </td>
                                <td class="suporte-titulo">
                                    <a href="{{ route('suporte-insert', $demanda->uid) }}" class="fw-medium">
                                        @if($demanda->temPendencia())
                                            <i class="ri-alert-fill text-warning me-1" title="Nova atividade após encerramento"
                                                aria-label="Nova atividade após encerramento"></i>
                                        @endif
                                        #{{ $demanda->id }} · {{ $demanda->titulo }}
                                    </a>
                                </td>
                                <td>{{ $demanda->autor?->name }}</td>
                                <td><span class="badge bg-{{ $demanda->status->cor() }}-subtle text-{{ $demanda->status->cor() }}">{{ $demanda->status->label() }}</span></td>
                                <td>{{ $demanda->atividades_count }}</td>
                                <td>{{ $demanda->classificacao?->label() ?? '—' }}</td>
                                <td>{{ $demanda->horas_gastas ? $demanda->horas_gastas.' h' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma demanda encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <small class="text-muted">{{ $demandas->total() }} demanda(s)</small>
                <div>{{ $demandas->links() }}</div>
            </div>
        </div>
    </div>
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
</script>
@endscript
