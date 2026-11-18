@extends('layouts.email')

@section('content')
<h3>Olá {{ $user_data['name'] }} </h3>
<p>
  Seu e-mail {{ $user_data['email'] }}, foi cadastrado como novo usuário no sistema da Rede Metrológica RS.
</p>
<br>

<p>
  Para acessar o sistema pela primeira vez, clique no link abaixo e faça login com os seguintes dados: <br><br>

  Link: <a href="{{ env('APP_URL').'/painel' }}">{{ env('APP_URL').'/painel' }}</a> <br>
  Email: {{ $user_data['email'] }} <br>
  Senha: {{ $user_data['password'] }}
</p>
<br>
<p>
  Obs: Essa senha é temporária e é só válida para o primeiro acesso.
</p>
<br>
<p>Atenciosamente,<br>Equipe Rede Metrológica RS</p>
@endsection
