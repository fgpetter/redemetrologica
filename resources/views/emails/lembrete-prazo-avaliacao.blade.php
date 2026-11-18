@extends('layouts.email')

@section('content')
<h3>Lembrete de prazo - {{ $rotuloPrazo }}</h3>

<p>
  Informamos que o prazo para realização da ação referente a
  <strong>{{ $rotuloPrazo }}</strong>
  do laboratório <strong>{{ $avaliacao->laboratorio->nome_laboratorio }}</strong>
  é no dia <strong>{{ $dataPrazoFormatada }}</strong>.
</p>

<p>
  Este é um lembrete automático enviado com antecedência para apoio no cumprimento do cronograma da avaliação.
</p>

<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
