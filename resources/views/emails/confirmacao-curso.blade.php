<!DOCTYPE html>
<html lang="pt-br"><head><meta charset="UTF-8"></head>
<body style="background-color: #D4DADE; color: #5a6576; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <figure style="text-align: center;"><img src="{{ $message->embed(public_path('build/images/site/LOGO_REDE_COLOR_150x376.png')) }}" alt="Rede Metrológica RS" width="150"></figure>
    <div style="background-color: #fff; padding: 20px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <p>Informamos que o Curso {{ $curso }} em {{ $dataInicio }} está confirmado. O acesso será encaminhado por e-mail até dois dias antes do treinamento.</p>
        <p>Procederemos com o envio da cobrança relativa à sua inscrição. Em caso desistência solicitamos a gentileza de formalizar o pedido de cancelamento pois pode inviabilizar a realização do curso.</p>
        <p>Estou à disposição para qualquer esclarecimento.</p>
    </div>
    <div style="text-align: center;"><span style="font-size: 12px;">© {{ date('Y') }} Sistema Rede Metrológica RS.</span></div>
</div></body></html>
