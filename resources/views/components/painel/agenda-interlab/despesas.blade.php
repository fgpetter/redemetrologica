@if ($agendainterlab->id)
<div class="row">
  <div class="col-12">
    @if ($agendainterlab->ano_referencia)
      <a class="btn btn-outline-primary mb-3" href="{{ route('agenda-interlab-fr135-agenda', $agendainterlab->uid) }}">Baixar FR-135 desta agenda</a>
    @else
      <p class="text-muted">Preencha o ano de referência para gerar o FR-135.</p>
    @endif
    <livewire:interlab.despesa-lista :agendaInterlabId="$agendainterlab->id" />
    <livewire:interlab.despesa-modal :agendaInterlabId="$agendainterlab->id" />
  </div>
</div>
@endif
