<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
</head>
<body style="background-color: #D4DADE; color: #5a6576; font: 16px/1.6 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
  <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="margin-top: 1.8rem; margin-bottom: 1.8rem;">
      <figure style="text-align: center;">
        <img src="http://localhost/build/images/site/LOGO_REDE_COLOR.png" alt="Rede Metrológica RS" width="140px">
      </figure>
    </div>

    <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
      <h1>Olá</h1>
      <p>Você está recebendo esse e-mail pois houve uma solicitação de recuperação de senha em seu cadastro na Rede Metrológica RS.</p>
      <br>

      <div style="text-align: center;">
        <a style="padding: 8px 14px; background-color: #5a6576; color: #fff; text-decoration: none; border-radius: 3px;" 
          href="{{ $actionUrl }}">Recuperar senha</a>
      </div>
      <br>
      <p>Esse link expira em 60 minutos</p>
      <p>Se você não solicitou a recuperação de senha, por favor, ignore esse e-mail.</p>
      <br>
      <p>Atenciosamente,<br>Equipe Rede Metrológica RS</p>
      <hr style="border: 1px solid #5a6576; margin: 20px 0;">
      <p style="font-size: 14px;">Se você não conseguir acessar o link, copie e cole o link abaixo no seu navegador:
        <br>
        <span style="word-break: break-all;" > {{ $actionUrl }} </span>
      </p>
    </div>
    <div style="text-align: center;"><span style="font-size: 12px;">© 2025 Sistema Rede Metrológica RS.</sp></div>
  </div>
</body>
</html>