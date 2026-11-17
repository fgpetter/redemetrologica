@extends('layouts.email')

@section('content')
<h3>Certificado de Participação - {{ $dadosDoc->content['interlab_nome'] }}</h3>
<p>É com grande satisfação que informamos que o seu Certificado de Participação - {{ $dadosDoc->content['interlab_nome'] }} está disponível para download.</p>

<p style="text-align: center; margin: 30px 0;">
  <a href="{{ route('dados-doc.download', $dadosDoc->link) }}"
     target="_blank"
     style="display: inline-block; padding: 12px 30px; background-color: #0056b3; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
    Baixar Certificado
  </a>
</p>

<p style="font-size: 12px; color: #888; text-align: center;">
  Ou copie e cole o link abaixo no seu navegador:<br>
  <a href="{{ route('dados-doc.download', $dadosDoc->link) }}" style="color: #0056b3; word-break: break-all;">{{ route('dados-doc.download', $dadosDoc->link) }}</a>
</p>

<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
