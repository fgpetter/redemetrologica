<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
</head>
<body style="background-color: #D4DADE; color: #5a6576; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
  <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="margin-top: 1.8rem; margin-bottom: 1.8rem;">
      <figure style="text-align: center;">
        <img src="{{ $message->embed(public_path('build/images/site/LOGO_REDE_COLOR.png')) }}" alt="Rede Metrológica RS" width="140px" style="max-width: 50%">
      </figure>
    </div>

    <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
      <h3>Confirmação de Inscrição - {{ $dados_email['curso_nome'] }}</h3>
      <p>Olá {{ $dados_email['participante_nome'] }},</p>
      <p>Sua inscrição no curso foi realizada com sucesso!</p>
      
      <h4>Dados da Inscrição:</h4>
      <p>
        <strong>Curso:</strong> {{ $dados_email['curso_nome'] }} <br>
        <strong>Data:</strong> {{ $dados_email['curso_data'] }} <br>
        @if($dados_email['empresa_nome'])
          <strong>Empresa:</strong> {{ $dados_email['empresa_nome'] }} <br>
        @endif
        <strong>E-mail:</strong> {{ $dados_email['participante_email'] }} <br>
        <strong>Telefone:</strong> {{ $dados_email['participante_telefone'] }}
      </p>

      <p>Para acessar mais informações sobre o curso, <a href="{{ $dados_email['link_curso'] }}" style="color: #0d6efd;">clique aqui</a>.</p>
      
      <br>
      <p>
        Atenciosamente,<br>
        Equipe Rede Metrológica RS
      </p>
    </div>
    <div style="text-align: center;"><span style="font-size: 12px;">© {{ date('Y') }} Sistema Rede Metrológica RS.</span></div>
  </div>
</body>
</html>
