@extends('layouts.email')

@section('content')
<h3>Olá {{ $dados_email['pessoa_convidada'] }} </h3>
<p> Você foi pré-inscrito por <strong>{{ $dados_email['pessoa_que_convida'] }}</strong> para participar do
<u>{{ $dados_email['nome_interlab'] }}</u> que será realizado a partir do dia <strong>{{ $dados_email['data_inicio'] }}</strong>.</p>

<p>Para ver mais detalhes e acessar documentos desse interlaboratorial <a href="{{ $dados_email['link_interlab'] }}">CLIQUE AQUI</a></p>

<p>Para confirmar sua participação, clique no link abaixo e confirme seus dados:</p>
<p style="text-align: center;"><a href="{{ $dados_email['link'] }}"
    style="background-color:#4AB0C1; color: #fff; padding: 10px; border-radius: 3px;text-decoration: none;">
    CONFIRMAR INSCRIÇÃO
  </a>
</p>
<br>

<small style="color:#5a6576" >Caso o link não funcione, copie e cole o endereço abaixo em seu navegador:</small> <br>
<small style="color:#5a6576" >{{ $dados_email['link'] }}</small>

<hr style="border: 0; border-top: 1px solid #a3a6aa;"><br>

<strong>IMPORTANTE:</strong> <br>
<p style="font-size: 13px; line-height: 21px;">
  Todos os cadastros em eventos na Rede Metrológica RS, agora, são realizados através do painel do cliente. <br>
  <strong>Se você ainda não tem cadastro</strong> clique na opção registre-se para criar um novo cadastro. <br>
</p>
<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
