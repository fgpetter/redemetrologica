
<!DOCTYPE html>
<html>
<head>
    <title>Certificado de Participação</title>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            margin: 40px;
        }
        .certificate-container {
            border: 10px solid #ccc;
            padding: 50px;
            width: 800px;
            margin: 0 auto;
        }
        .logo {
            width: 150px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 50px;
            color: #333;
        }
        h2 {
            font-size: 25px;
            color: #555;
        }
        p {
            font-size: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="certificate-container">

        {{-- <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo"> --}}
        <h1>Certificado de Participação</h1>
        <h2>A Rede Metrológica de Minas Gerais confere este certificado a</h2>
        <p><strong>{{ $participante->pessoa->nome_razao }}</strong></p>
        <p>pela sua participação no programa de interlaboratório</p>
        
    </div>
</body>
</html>
