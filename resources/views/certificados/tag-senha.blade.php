<!DOCTYPE html>
<html>

<head>
    <title>Tag Senha - Interlab</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 12pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 30px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 10px;
            vertical-align: middle;
            text-align: center;
        }

        .logo-cell {
            width: 25%;
        }

        .title-cell {
            width: 50%;
            font-weight: bold;
            font-size: 14pt;
        }

        .doc-info-cell {
            width: 25%;
            font-size: 9pt;
            text-align: center;
        }

        .doc-info-cell div {
            margin-bottom: 2px;
        }

        .watermark {
            position: fixed;
            top: -1cm;
            left: -1cm;
            width: 210mm;
            height: 297mm;
            opacity: 0.45;
            z-index: -1;
            object-fit: cover;
        }

        .main-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .program-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 50px;
            text-transform: uppercase;
        }

        .info-group {
            margin-bottom: 20px;
            font-size: 14pt;
        }

        .label {
            color: #0000FF;
            font-weight: bold;
        }

        .value {
            color: #000;
        }

        .tag-container {
            text-align: center;
            margin-top: 60px;
        }

        .tag-box {
            display: inline-block;
            border: 1px solid #000;
            padding: 10px 40px;
            font-size: 24pt;
            font-weight: bold;
        }

        .tag-label {
            color: #0056b3;
            font-weight: bold;
            margin-right: 20px;
        }

        .tag-value {
            color: #0000FF;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 10pt;
        }

        .footer-table td {
            vertical-align: top;
        }

        .footer-left {
            text-align: left;
        }

        .footer-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <img src="{{ resource_path('images/certificados/marcadagua.png') }}" class="watermark">

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ resource_path('images/certificados/LOGO_REDE_COLOR.png') }}" alt="Logo Rede Metrológica RS"
                    style="width: 150px;">
            </td>
            <td class="title-cell">
                CARTA SENHA
            </td>
            <td class="doc-info-cell">
                <div>FR-70</div> <!-- TODO: Verificar qual informação adicionar aqui -->
                <div>REVISÃO 04</div> <!-- TODO: Verificar como controlar a revisão -->
                <div>OUT/2024</div>
            </td>
        </tr>
    </table>

    <div class="main-title">
        CÓDIGO DE IDENTIFICAÇÃO DO LABORATÓRIO
    </div>

    <div class="program-title">
        {{ $participante->agendaInterlab->interlab->nome }}
    </div>

    <div class="info-group">
        <span class="label">Empresa:</span>
        <span class="value">{{ $participante->empresa->nome_razao }}</span>
    </div>

    <div class="info-group">
        <span class="label">Laboratório:</span>
        <span class="value">{{ $participante->laboratorio->nome }}</span>
    </div>

    <div class="info-group">
        <span class="label">CNPJ:</span>
        <span class="value">{{ $participante->empresa->cpf_cnpj }}</span>
    </div>

    <div class="info-group">
        <span class="label">Opção Contratada:</span>
        <span class="value">{{ $participante->informacoes_inscricao }}</span>
    </div>

    <div class="tag-container">
        <div class="tag-box">
            <span class="tag-label" style="color: #006699; font-size: 28pt;">TAG:</span>
            <span class="tag-value" style="color: #0000FF; font-size: 28pt;">{{ $participante->tag_senha }}</span>
        </div>
    </div>

    <div class="footer">
        <table style="width: 100%">
            <tr>
                <td class="footer-left">Rede Metrológica RS</td>
                <td class="footer-right">1 de 1</td>
            </tr>
        </table>
    </div>
</body>

</html>