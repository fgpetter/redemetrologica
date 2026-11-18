@extends('layouts.email')

@section('content')
<h2>Novo contato recebido:</h2>
<p><strong>Nome:</strong> {{ $dados['name'] }}</p>
<p><strong>Email:</strong> {{ $dados['email'] }}</p>
<p><strong>Telefone:</strong> {{ $dados['phone'] }}</p>
<p><strong>Mensagem:</strong><br>{{ $dados['message'] }}</p>

@if(!empty($dados['areas']))
  <p><strong>Áreas selecionadas:</strong></p>
  <ul>
    @foreach($dados['areas'] as $area)
      <li>{{ $area }}</li>
    @endforeach
  </ul>
@endif
@endsection
