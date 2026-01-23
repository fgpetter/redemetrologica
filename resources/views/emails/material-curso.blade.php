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
      <h3>Materiais do Curso - {{ $inscrito->agendaCurso->curso->descricao }}</h3>
      <p>Olá {{ $inscrito->nome }},</p>
      <p>Conforme solicitado, seguem os links para download dos materiais do curso:</p>
      
      <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #0056b3;">
        <ul style="padding-left: 20px;">
          @foreach($materiais as $material)
            <li style="margin-bottom: 10px;">
              <strong>{{ $material->descricao ?: 'Material' }}:</strong> <br>
              <a href="{{ asset('curso-material/' . $material->arquivo) }}" target="_blank" style="color: #0056b3; text-decoration: underline;">
                Clique aqui para baixar
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      <p>Caso tenha alguma dúvida, entre em contato conosco.</p>
      
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
