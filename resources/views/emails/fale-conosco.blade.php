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
      <h2>Novo contato recebido:</h2>
      <p><strong>Nome:</strong> {{ $dados['name'] }}</p>
      <p><strong>Email:</strong> {{ $dados['email'] }}</p>
      <p><strong>Telefone:</strong> {{ $dados['phone'] }}</p>
      <p><strong>Mensagem:</strong><br>{{ $dados['message'] }}</p>
      
      @if(!empty($dados['areas']))
        <p><strong>Áreas selecionadas:</strong></p>
        <ul>
          @foreach($dados['areas'] as $area)
            <li>{{ $area }}</li>
          @endforeach
        </ul>
      @endif
    </div>
    <div style="text-align: center;"><span style="font-size: 12px;">© 2025 Sistema Rede Metrológica RS.</span></div>
  </div>
</body>
</html> 