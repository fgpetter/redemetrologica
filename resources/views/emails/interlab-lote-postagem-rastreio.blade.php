@extends('layouts.email')

@section('content')
<h3>Atualização de rastreio — {{ $codigoObjeto }}</h3>
<p>{{ $agendaInterlab->interlab->nome }} - {{ $agendaInterlab->ano_referencia }}</p>

<p>Bom dia {{ $responsavelTecnico }}.</p>

<p>
  A postagem com suas amostras enviadas para <strong>{{ $nomeLaboratorio }}</strong> no endereço:<br>
  {{ $enderecoCompleto }}<br>
  foi atualizada.
</p>

<p>
  O status atual do objeto <strong>{{ $codigoObjeto }}</strong> é {{ $statusAtual }}.
  Acesse
  <a href="{{ $urlRastreamento }}" target="_blank" style="color: #0056b3;">Rastreamento Correios</a>
  para mais detalhes.
</p>

<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
