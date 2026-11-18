@extends('layouts.email')

@section('content')
<h3>Houve um erro ao cadastrar um usuário no sistema.</h3>
<p>
  O usuário <strong> {{ $content['pessoa'] }} </strong> tentou se cadastrar no sistema com um e-mail inválido. <br>
  Esse erro ocorreu pois o e-mail cadastrado anteriormente no sistema era:
  <span style="background-color:rgb(236, 216, 98);">{{ $content['email'] }}</span> e falhou ao ser migrado. <br><br>
  O usuário foi informado do erro com a seguinte mensagem: <br>
  <blockquote style="font-size: 0.8rem;"><i>
    O CPF informado já possui cadastro em nosso sistema, mas ainda sem usuário para login.
    Solicite a atualização do seu cadastro através do email contato@redemetrologica.com.br <br>
  </i></blockquote>
</p>
<p>
  Para editar o cadastro da pessoa <a href="{{ env('APP_URL').'/painel/pessoa/insert/'.$content['pessoa_uid'] }}"> CLIQUE AQUI </a>

</p>
<p>Atenciosamente,<br>Equipe Rede Metrológica RS</p>
@endsection
