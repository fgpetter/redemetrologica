<div class="table-responsive mt-3">
    <table class="table table-striped align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th scope="col">Laboratório</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lote->itens as $item)
                <tr wire:key="lote-postagem-item-{{ $item->id }}">
                    <td class="text-wrap">{{ $item->destinatario['nome'] ?? '—' }}</td>
                    <td>
                        {{ $item->statusVisualClassificacao()->label() }}
                        @if ($detalhe = $item->detalheProximaTentativa())
                            <br>
                            <span class="text-muted small">{{ $detalhe }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
