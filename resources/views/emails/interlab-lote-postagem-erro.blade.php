@extends('layouts.email')

@section('content')
<h3>Lote de postagem encerrado com erro</h3>

<p>O lote <strong>{{ $lote->nome }}</strong> ({{ $lote->uid }}) não pôde continuar.</p>

<p>
  <strong>Etapa:</strong> {{ $lote->etapa_erro }}<br>
  <strong>Causa:</strong> {{ $lote->erro_mensagem }}
</p>

<p>Os resultados já obtidos foram preservados. O lote não será retomado automaticamente.</p>

<br>
<p>
  Atenciosamente,<br>
  Sistema Rede Metrológica RS
</p>
@endsection
