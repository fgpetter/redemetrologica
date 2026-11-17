@extends('layouts.email')

@section('content')
<h3>Nova inscrição em {{ $dados_email['interlab_nome'] }}</h3>
<p>
  Uma nova inscrição em interlabo foi enviada por {{ $dados_email['nome_da_pessoa'] }}
</p>
<p>
  Laboratório: {{ $dados_email['laboratorio_nome'] }} <br>
  Empresa: {{ $dados_email['empresa_nome'] }} - {{ $dados_email['empresa_cnpj'] }} <br>
  Email: {{ $dados_email['laboratorio_email'] }} <br>
  Telefone: {{ $dados_email['laboratorio_telefone'] }} <br>
  Endereço para envio de material: {{ $dados_email['laboratorio_endereco'] }} <br>
</p>
<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
