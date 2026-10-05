<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
</head>
<body style="background-color: #D4DADE; color: #5a6576; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
  <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
      <h3>Documentos do lote de postagem</h3>

      <p>O lote <strong>{{ $lote->nome }}</strong> ({{ $lote->uid }}) foi concluído com sucesso.</p>

      <p>
        Seguem em anexo os PDFs das etiquetas e o PDF de conteúdo do lote.
      </p>

      <br>
      <p>
        Atenciosamente,<br>
        Sistema Rede Metrológica RS
      </p>
    </div>
    <div style="text-align: center;"><span style="font-size: 12px;">© {{ date('Y') }} Sistema Rede Metrológica RS.</span></div>
  </div>
</body>
</html>
