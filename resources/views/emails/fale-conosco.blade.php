<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Rede Metrológica RS - Novo Contato do Site</title>
  <style type="text/css">

    body, html {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #f2f6fa; 
    }

    .header {
      background-color: #003366;
      color: #ffffff;
      text-align: center;
      padding: 20px;
    }
    .header h1 {
      margin: 0;
      font-size: 24px;
      letter-spacing: 1px;
    }

    .container {
      max-width: 600px;
      margin: 20px auto;
      background-color: #ffffff;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      overflow: hidden; 
    }

    .content {
      padding: 20px;
    }

    h2 {
      color: #005b99; 
      margin-top: 0;
      margin-bottom: 20px;
      font-size: 20px;
    }

    p {
      color: #333333;
      line-height: 1.5;
      margin: 10px 0;
    }

    strong {
      color: #005b99; 
    }

    ul {
      list-style-type: disc;
      padding-left: 20px;
      margin: 10px 0;
    }
    li {
      margin-bottom: 5px;
    }

    .footer {
      background-color: #005b99;
      color: #ffffff;
      text-align: center;
      padding: 10px;
    }
    .footer p {
      margin: 0;
      font-size: 14px;
    }
  </style>
</head>
<body>

  <div class="header">
    <h1>Rede Metrológica RS</h1>
  </div>

  <div class="container">

    <div class="content">
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
  </div>

  <div class="footer">
    <p>&copy; {{ date('Y') }} Rede Metrológica RS. Todos os direitos reservados.</p>
  </div>

</body>
</html>
