@props(['fornecedor'])

@php
    $avaliacoes = $fornecedor->avaliacoes()
        ->whereNotNull('media')
        ->with(['lancamento.agendaInterlab.interlab'])
        ->get()
        ->sortByDesc(fn ($a) => $a->lancamento?->agendaInterlab?->ano_referencia ?? 0)
        ->values();
@endphp

<div class="card shadow-none border overflow-hidden card-border-info mt-3">
    <div class="card-header d-flex justify-content-between align-items-start bg-info-subtle py-2">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm">
                <span class="avatar-title bg-dark-subtle text-dark rounded-circle fs-3">
                    <i class="ph-star"></i>
                </span>
            </div>
            <h4 class="card-title mb-0">Avaliações</h4>
        </div>
    </div>

    <div class="px-1">
        <ul class="list-group list-group-flush">
            @forelse ($avaliacoes as $avaliacao)
                <li class="list-group-item">
                    <a href="{{ route('agenda-interlab-insert', ['agendainterlab' => $avaliacao->lancamento->agendaInterlab->uid]) }}">
                        {{ $avaliacao->lancamento->agendaInterlab->interlab->nome }} - {{ $avaliacao->lancamento->agendaInterlab->ano_referencia }}
                    </a>
                    | {{ number_format($avaliacao->media, 1, '.', '') }}
                </li>
            @empty
                <li class="list-group-item">
                    <p class="m-0">Não há avaliações cadastradas</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>
