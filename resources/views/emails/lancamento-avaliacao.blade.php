@extends('layouts.email')

@section('content')
<h3>Novo lançamento financeiro - Avaliação</h3>
<p>
  Foi gerado um lançamento financeiro para a avaliação abaixo.
</p>
<p>
  Laboratório: {{ $dados_email['laboratorio_nome'] }} <br>
  Data início: {{ $dados_email['data_inicio'] }} <br>
  Valor: R$ {{ $dados_email['valor'] }} <br>
  Histórico: {{ $dados_email['historico'] }} <br>
</p>
<p>
  <a href="{{ $dados_email['link_lancamento'] }}">Ver lançamento no painel</a>
</p>
<br>
<p>
  Atenciosamente,<br>
  Equipe Rede Metrológica RS
</p>
@endsection
