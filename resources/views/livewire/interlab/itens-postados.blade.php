<div>
    @forelse ($lotesConcluidos as $lote)
        <div class="card mt-3" wire:key="itens-postados-lote-{{ $lote->id }}">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-1">{{ $lote->nome }}</h5>
                    <span class="text-muted small">
                        {{ $lote->created_at?->format('d/m/Y H:i') }} · {{ $lote->itens->count() }} itens
                    </span>
                </div>
                <a
                    href="{{ route('agenda-interlab-lote-postagem-documentos', $lote) }}"
                    class="btn btn-sm btn-outline-primary text-nowrap"
                >
                    <i class="ri-download-2-line align-bottom"></i> Baixar documentos (.zip)
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th scope="col">Laboratório</th>
                                <th scope="col">Código do objeto</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lote->itens as $item)
                                <tr wire:key="itens-postados-item-{{ $item->id }}">
                                    <td class="text-wrap">{{ $item->destinatario['nome'] ?? '—' }}</td>
                                    <td>{{ $item->codigo_objeto ?? '—' }}</td>
                                    <td>{{ $item->status->label() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info mt-3 mb-0">Nenhum lote de postagem concluído nesta agenda.</div>
    @endforelse
</div>
