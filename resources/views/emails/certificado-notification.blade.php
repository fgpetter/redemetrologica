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
      <h3>Certificado Disponível - {{ $dadosDoc->content['curso_nome'] }}</h3>
      <p>Olá {{ $dadosDoc->content['participante_nome'] }},</p>
      <p>Seu certificado para o curso <strong>{{ $dadosDoc->content['curso_nome'] }}</strong> já está disponível para download.</p>
      
        <a href="{{ route('dados-doc.download', $dadosDoc->link) }}" 
           target="_blank"
           style="display: inline-block; padding: 12px 30px; background-color: #0056b3; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
          Baixar Certificado
        </a>
      </p>

      <p style="font-size: 12px; color: #888; text-align: center;">
        Ou copie e cole o link abaixo no seu navegador:<br>
        <a href="{{ route('dados-doc.download', $dadosDoc->link) }}" style="color: #0056b3; word-break: break-all;">{{ route('dados-doc.download', $dadosDoc->link) }}</a>
      </p>
      
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
