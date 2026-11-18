@extends('layouts.email')

@section('content')
<h3>Certificado Disponível - {{ $dadosDoc->content['curso_nome'] }}</h3>
<p>Olá {{ $dadosDoc->content['participante_nome'] }},</p>
<p>Seu certificado para o curso <strong>{{ $dadosDoc->content['curso_nome'] }}</strong> já está disponível para download.</p>
<p><strong>IMPORTANTE:</strong> se você já recebeu esse certificado anteriormente, esta é uma versão atualizada.</p>

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
