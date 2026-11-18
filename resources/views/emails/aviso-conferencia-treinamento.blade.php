@extends('layouts.email')

@section('content')
<h3>Confira o link e materiais para {{ $dados['treinamento'] }}</h3>
  <p>Dia {{ $dados['data_anterior'] }}, serão enviados os materiais e link do zoom para o treinamento {{ $dados['treinamento'] }}.</p>
<p>Atenciosamente,<br>Equipe Rede Metrológica RS</p>
@endsection
