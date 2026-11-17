@extends('layouts.email')

@section('content')
<h3>Demanda de suporte atualizada</h3>
<p>A demanda <strong>{{ $titulo }}</strong> que você acompanha foi atualizada.</p>

<p style="text-align: center; margin: 30px 0;">
  <a href="{{ $url }}"
     target="_blank"
     style="display: inline-block; padding: 12px 30px; background-color: #0056b3; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
    Acessar demanda
  </a>
</p>

<p style="font-size: 12px; color: #888; text-align: center;">
  Ou copie e cole o link abaixo no seu navegador:<br>
  <a href="{{ $url }}" style="color: #0056b3; word-break: break-all;">{{ $url }}</a>
</p>

<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
