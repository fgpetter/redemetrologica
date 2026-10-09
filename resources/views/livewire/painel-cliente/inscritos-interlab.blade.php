<div>
    @if ($interlabs->isNotEmpty())
        <div class="col-12 col-xxl-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="h5 mb-3">PEPs e Interlaboratoriais inscritos por você:</h5>
                    <ul class="list-group">
                        @foreach ($interlabs->groupBy('agendaInterlab.id') as $agendaGroup)
                            <li class="list-group-item" wire:key="agenda-{{ $agendaGroup->first()->agendaInterlab->id }}">
                                <a href="{{ route('painel-meus-peps') }}#agenda-{{ $agendaGroup->first()->agendaInterlab->id }}" class="link-primary">
                                    {{ $agendaGroup->first()->agendaInterlab->interlab->nome }} - {{ \Carbon\Carbon::parse($agendaGroup->first()->agendaInterlab->data_inicio)->format('Y') }}
                                    @if ($agendaGroup->first()->agendaInterlab->status === 'CONCLUIDO')
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary ms-1">Concluído</span>
                                    @endif
                                    <small class="text-muted ms-1">ver detalhes</small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>
