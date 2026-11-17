@extends('layouts.email')

@section('content')
<h3>Lembrete de prazo não enviado ao laboratório</h3>

<p>
  O laboratório <strong>{{ $avaliacao->laboratorio->nome_laboratorio }}</strong>
  não possui e-mail cadastrado. O lembrete abaixo não pôde ser enviado:
</p>

<p>
  <strong>{{ $rotuloPrazo }}</strong> — prazo em {{ $dataPrazoFormatada }}
</p>

<p>
  Avaliação: {{ $avaliacao->uid }}
</p>

<br>
<p>
  Atenciosamente,<br>
  Sistema Rede Metrológica RS
</p>
@endsection
