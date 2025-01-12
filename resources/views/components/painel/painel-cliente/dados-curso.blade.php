<h5 class="mb-4 text-primary">Informações do curso:</h5>
<h5>{{ $curso->curso->descricao }}</h5>
<div class="hstack gap-2 gap-md-3 flex-wrap mb-4">
  <div class="text-muted">Tipo: {{ $curso->tipo_agendamento }}</div>
  <div class="vr"></div>
  <div class="text-muted">Inicio: 
    <span class="fw-medium"> @if($curso->data_inicio) {{ \Carbon\Carbon::parse($curso->data_inicio)->format('d/m/Y') }} @endif </span>
  </div>
  <div class="vr"></div>
  <div class="text-muted">Termino: 
    <span class="fw-medium">@if($curso->data_fim) {{ \Carbon\Carbon::parse($curso->data_fim)->format('d/m/Y') }} @endif</span>
  </div>
  <div class="vr"></div>
  @if($curso->status == 'AGENDADO')
    <div class="badge rounded-pill bg-info fs-12">Agendado</div>
  @endif
  @if($curso->status == 'CONFIRMADO')
    <div class="badge rounded-pill bg-success fs-12">Confirmado</div>
  @endif
</div>

<h6 class="fw-semibold text-uppercase mb-1">Objetivo</h6>
<p class="pe-4">{{ $curso->curso->objetivo }}</p>
<br>
<h6 class="fw-semibold text-uppercase mb-1">Conteúdo programático</h6>
<p class="pe-4">{{ $curso->curso->conteudo_programatico }}</p>
