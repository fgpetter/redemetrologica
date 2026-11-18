@extends('layouts.email')

@section('content')
<h3>Código de identificação de analista - {{ $dadosDoc->content['interlab_nome'] }}</h3>

Olá {{ $dadosDoc->content['analista_nome'] }},
<p>Segue código de identificação (senha) do analista {{ $dadosDoc->content['analista_nome'] }} - {{ $dadosDoc->content['laboratorio_nome'] }}.</p>

<p style="margin: 30px 0;">
  <a href="{{ route('dados-doc.download', $dadosDoc->link) }}"
     target="_blank"
     style="display: inline-block; padding: 12px 30px; background-color: #0056b3; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
    Baixar Carta Senha
  </a>
</p>

<p style="font-size: 12px; color: #888;">
  Ou copie e cole o link abaixo no seu navegador:<br>
  <a href="{{ route('dados-doc.download', $dadosDoc->link) }}" style="color: #0056b3; word-break: break-all;">{{ route('dados-doc.download', $dadosDoc->link) }}</a>
</p>

<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
