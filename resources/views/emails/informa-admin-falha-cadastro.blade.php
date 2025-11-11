<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
</head>
<body style="background-color: #D4DADE; color: #5a6576; font: 16px/1.6 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
  <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="margin-top: 1.8rem; margin-bottom: 1.8rem;">
      <figure style="text-align: center;">
        <img src="{{ asset('build\images\site\LOGO_REDE_COLOR.png') }}" alt="Rede Metrológica RS" width="140px">
      </figure>
    </div>

    <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
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
        Para editar o cadastro da pessoa <a href="{{ 'https://redemetrologica.com.br/painel/pessoa/insert/'.$content['pessoa_uid'] }}"> CLIQUE AQUI </a>
      </p>
      <p>Atenciosamente,<br>Equipe Rede Metrológica RS</p>
    </div>
    <div style="text-align: center;"><span style="font-size: 12px;">© 2025 Sistema Rede Metrológica RS.</sp></div>
  </div>
</body>
</html>