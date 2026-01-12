<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
</head>
<body style="background-color: #D4DADE; color: #5a6576; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
  <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="margin-top: 1.8rem; margin-bottom: 1.8rem;">
      <figure style="text-align: center;">
        <img src="{{ asset('build\images\site\LOGO_REDE_COLOR.png') }}" alt="Rede Metrológica RS" width="140px" style="max-width: 50%">
      </figure>
    </div>

    <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
      <h3>Olá {{ $dados_email['pessoa_convidada'] }} </h3>
      <p> Você foi pré-inscrito por <strong>{{ $dados_email['pessoa_que_convida'] }}</strong> para participar do curso 
        <u>{{ $dados_email['nome_curso'] }}</u> que será realizado dos dias 
        <strong>{{ $dados_email['data_inicio'] }} a {{ $dados_email['data_fim'] }}</strong> {{ $dados_email['horario'] }}.</p>

      <p>Para confirmar sua inscrição, clique no link abaixo e confirme seus dados para a matricula:</p>
      <p style="text-align: center;">
        <a href="{{ $dados_email['link'] }}" 
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
        Todos os cadastros em cursos na Rede Metrológica RS, agora, são realizados através do painel do cliente. <br>
        <strong>Se você ainda não tem cadastro</strong> clique na opção registre-se para criar um novo cadastro. <br>
      </p>
      <br>
      <p>
        Atenciosamente,<br>
        Equipe Rede Metrológica RS
      </p>
    </div>
    <div style="text-align: center;"><span style="font-size: 12px;">© 2025 Sistema Rede Metrológica RS.</sp></div>
  </div>
</body>
</html>