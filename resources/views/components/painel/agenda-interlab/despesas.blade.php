@if ($agendainterlab->id)
<div class="row">
  <div class="col-12">
    <livewire:interlab.despesa-lista :agendaInterlabId="$agendainterlab->id" />
    <livewire:interlab.despesa-modal :agendaInterlabId="$agendainterlab->id" />
  </div>
</div>
@endif
