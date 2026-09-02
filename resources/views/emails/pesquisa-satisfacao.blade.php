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
      <h3>Pesquisa de Satisfação - {{ $avaliacao->laboratorio->nome_laboratorio }}</h3>

      <p>
        Segue o link para responder a pesquisa de satisfação do laboratório
        {{ $avaliacao->laboratorio->nome_laboratorio }}
        referente ao período
        {{ $avaliacao->data_inicio ? \Carbon\Carbon::parse($avaliacao->data_inicio)->format('d/m/Y') : '' }}
        a
        {{ $avaliacao->data_fim ? \Carbon\Carbon::parse($avaliacao->data_fim)->format('d/m/Y') : '' }}.
      </p>

      <p style="margin: 30px 0;">
        <a href="{{ route('pesquisa-satisfacao.show', $avaliacao) }}"
           target="_blank"
           style="display: inline-block; padding: 12px 30px; background-color: #0056b3; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
          Responder pesquisa
        </a>
      </p>

      <p style="font-size: 12px; color: #888;">
        Ou copie e cole o link abaixo no seu navegador:<br>
        <a href="{{ route('pesquisa-satisfacao.show', $avaliacao) }}" style="color: #0056b3; word-break: break-all;">{{ route('pesquisa-satisfacao.show', $avaliacao) }}</a>
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
