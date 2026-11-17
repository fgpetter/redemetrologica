@extends('layouts.email')

@section('content')
<h3>Pesquisa de Satisfação - {{ $avaliacao->laboratorio->nome_laboratorio }}</h3>

<p>
  Segue o link para responder a pesquisa de satisfação do laboratório
  {{ $avaliacao->laboratorio->nome_laboratorio }}
  referente ao período
  {{ $avaliacao->data_inicio ? \Carbon\Carbon::parse($avaliacao->data_inicio)->format('d/m/Y') : '' }}
  a
  {{ $avaliacao->data_fim ? \Carbon\Carbon::parse($avaliacao->data_fim)->format('d/m/Y') : '' }}.
</p>

<p style="margin: 30px 0;">
  <a href="{{ route('pesquisa-satisfacao.show', $avaliacao) }}"
     target="_blank"
     style="display: inline-block; padding: 12px 30px; background-color: #0056b3; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
    Responder pesquisa
  </a>
</p>

<p style="font-size: 12px; color: #888;">
  Ou copie e cole o link abaixo no seu navegador:<br>
  <a href="{{ route('pesquisa-satisfacao.show', $avaliacao) }}" style="color: #0056b3; word-break: break-all;">{{ route('pesquisa-satisfacao.show', $avaliacao) }}</a>
</p>

<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
