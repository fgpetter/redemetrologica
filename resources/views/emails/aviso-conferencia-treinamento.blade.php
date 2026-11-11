<!DOCTYPE html>
<html lang="pt-br"><head><meta charset="UTF-8"></head>
<body style="background-color: #D4DADE; color: #5a6576; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
  <figure style="text-align: center;"><img src="{{ $message->embed(public_path('build/images/site/LOGO_REDE_COLOR_150x376.png')) }}" alt="Rede Metrológica RS" width="150"></figure>
  <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
    <h3>Confira o link e materiais para {{ $agenda->curso->descricao }}</h3>
      <p>Dia {{ $agenda->data_inicio->copy()->subDay()->format('d/m/Y') }}, serão enviados os materiais e link do zoom para o treinamento {{ $agenda->curso->descricao }}.</p>
    <p>Atenciosamente,<br>Equipe Rede Metrológica RS</p>
  </div>
  <div style="text-align: center;"><span style="font-size: 12px;">© {{ date('Y') }} Sistema Rede Metrológica RS.</span></div>
</div></body></html>
