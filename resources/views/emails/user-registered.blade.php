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
      <h3>Olá {{ $user_data['name'] }} </h3>
      <p>
        Seu e-mail {{ $user_data['email'] }}, foi cadastrado como novo usuário no sistema da Rede Metrológica RS.
      </p>
      <br>

      <p>
        Para acessar o sistema pela primeira vez, clique no link abaixo e faça login com os seguintes dados: <br><br>

        Link: <a href="https://redemetrologica.com.br/painel">https://redemetrologica.com.br/painel</a> <br>
        Email: {{ $user_data['email'] }} <br>
        Senha: {{ $user_data['password'] }}
      </p>
      <br>
      <p>
        Obs: Essa senha é temporária e é só válida para o primeiro acesso.
      </p>
      <br>
      <p>Atenciosamente,<br>Equipe Rede Metrológica RS</p>
    </div>
    <div style="text-align: center;"><span style="font-size: 12px;">© 2025 Sistema Rede Metrológica RS.</sp></div>
  </div>
</body>
</html>