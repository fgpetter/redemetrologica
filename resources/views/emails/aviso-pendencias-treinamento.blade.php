@extends('layouts.email')

@section('content')
<h3>Pendências para {{ $dados['treinamento'] }}</h3>
  <p><strong>Data do treinamento:</strong> {{ \Carbon\CarbonImmutable::parse($dados['data_curso'], 'America/Sao_Paulo')->format('d/m/Y') }}</p>
  <p>O e-mail de materiais e link de acesso não foram enviados aos inscritos. As informações estão faltantes ou inválidas.
    <br> Revise os dados e <strong>envie manualmente</strong> para cada inscrito.
  </p>
<p>Atenciosamente,<br>Equipe Rede Metrológica RS</p>
@endsection
