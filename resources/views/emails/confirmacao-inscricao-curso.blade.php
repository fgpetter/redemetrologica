@extends('layouts.email')

@section('content')
<h3>Confirmação de Inscrição - {{ $dados_email['curso_nome'] }}</h3>
<p>Olá {{ $dados_email['participante_nome'] }},</p>
<p>Sua inscrição no curso foi realizada com sucesso!</p>

<h4>Dados da Inscrição:</h4>
<p>
  <strong>Curso:</strong> {{ $dados_email['curso_nome'] }} <br>
  <strong>Data:</strong> {{ $dados_email['curso_data'] }} <br>
  @if($dados_email['empresa_nome'])
    <strong>Empresa:</strong> {{ $dados_email['empresa_nome'] }} <br>
  @endif
  <strong>E-mail:</strong> {{ $dados_email['participante_email'] }} <br>
  <strong>Telefone:</strong> {{ $dados_email['participante_telefone'] }}
</p>

<p>Para acessar mais informações sobre o curso, <a href="{{ $dados_email['link_curso'] }}" style="color: #0d6efd;">clique aqui</a>.</p>

<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
