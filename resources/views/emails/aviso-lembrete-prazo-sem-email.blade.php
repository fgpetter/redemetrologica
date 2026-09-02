<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
</head>
<body style="background-color: #D4DADE; color: #5a6576; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
  <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="margin-top: 1.8rem; margin-bottom: 1.8rem;">
      <figure style="text-align: center;">
        <img src="{{ $message->embed(public_path('build/images/site/LOGO_REDE_COLOR_150x376.png')) }}" alt="Rede Metrológica RS" width="140px" style="max-width: 50%">
      </figure>
    </div>

    <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
      <h3>Lembrete de prazo não enviado ao laboratório</h3>

      <p>
        O laboratório <strong>{{ $avaliacao->laboratorio->nome_laboratorio }}</strong>
        não possui e-mail cadastrado. O lembrete abaixo não pôde ser enviado:
      </p>

      <p>
        <strong>{{ $rotuloPrazo }}</strong> — prazo em {{ $dataPrazoFormatada }}
      </p>

      <p>
        Avaliação: {{ $avaliacao->uid }}
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
