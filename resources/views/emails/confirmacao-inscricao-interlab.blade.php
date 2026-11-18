@extends('layouts.email')

@section('content')
<h3>Confirmação de Inscrição - {{ $dados_email['interlab_nome'] }}</h3>
<p>Olá {{ $dados_email['nome_da_pessoa'] }},</p>
<p>Sua inscrição no interlaboratorial foi realizada com sucesso!</p>

<h4>Dados do Laboratório:</h4>
<p>
  Laboratório: {{ $dados_email['laboratorio_nome'] }} <br>
  Empresa: {{ $dados_email['empresa_nome'] }} <br>
  Responsável Técnico: {{ $dados_email['responsavel_tecnico'] }} <br>
  Email: {{ $dados_email['laboratorio_email'] }} <br>
  Telefone: {{ $dados_email['laboratorio_telefone'] }} <br>
  Endereço: {{ $dados_email['laboratorio_endereco'] }}
</p>

@if(! empty($dados_email['valor']) && $dados_email['valor'] > 0)
<h4>Valor da Inscrição:</h4>
<p>R$ {{ formataValorBr($dados_email['valor']) }}</p>
@endif

<p>Para acessar mais informações sobre o interlaboratorial, <a href="{{ $dados_email['link_interlab'] }}" style="color: #0d6efd;">clique aqui</a>.</p>

<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
