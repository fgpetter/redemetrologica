<!DOCTYPE html>
<html>

<head>
    <title>Certificado de Participação</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @page {
            size: A4 landscape;
            height: 100%;
            position: relative;
            margin: 0;
        }

        html {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            opacity: 0.45;
            z-index: -1;
        }

        .watermark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .certificate-container {
            padding-bottom: 40mm; /* ajuste conforme a altura do seu rodapé */
            box-sizing: border-box;
            width: 100%;
            height: 100%;
        }

         .footer {
            position: fixed;
            bottom: 0mm;
            left: 0;
            width: 100%;
        }

        .footer img {
            width: 90%;
            display: block;
            margin: 0 auto;
        }

        .cert-logo img {
            width: 300px;
        }

        .cert-signature img {
            max-height: 120px;
        }

        .second-page {
            page-break-before: always;
            position: relative;
            width: 100%;
            min-height: 100vh;
            padding: 30mm 20mm 45mm 20mm;
            text-align: left;
            box-sizing: border-box;
        }

        .second-page .watermark {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .second-page .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .second-page .field-label {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .second-page .field-value {
            margin-bottom: 1.25rem;
            white-space: pre-wrap;
        }
    </style>
</head>

<body class="text-center bg-white">
    <div class="watermark">
        @inlinedImage(resource_path('images/certificados/marcadagua.png'))
    </div>

    <div class="certificate-container p-2">

        <div style="position: fixed; left: 0; top: 50%; transform: translateY(-50%); writing-mode: vertical-lr; z-index: 999;">
            FR93 - rev01
        </div>

        <div class="text-center mb-3 cert-logo">
            @inlinedImage(resource_path('images/certificados/LOGO_REDE_COLOR.png'))
        </div>

        <div class="mt-2">
            <h1 class="display-4 font-weight-bold">CERTIFICADO</h1>
            <h2 class="h2 font-weight-bold my-3">Participação</h2>

            <p class="h3">Associação Rede de Metrologia e Ensaios do RS certifica que</p>

            <p class="h4 font-weight-bold my-3">{{ $dadosDoc->content['participante_nome'] }}</p>

            <p class="h3">Participou do curso de</p>

            <p class="h4 font-weight-bold mt-3 mb-3">{{ $dadosDoc->content['curso_nome'] }}</p>

            <p class="h6">{{ $dadosDoc->content['curso_data'] }}</p>

            @php
                setlocale(LC_TIME, 'pt_BR.UTF-8');
                $data = \Carbon\Carbon::now();
                $dataFormatada =
                    'Porto Alegre, ' .
                    $data->format('d') .
                    ' de ' .
                    ucfirst($data->translatedFormat('F')) .
                    ' de ' .
                    $data->format('Y');
            @endphp

            <p class="h6 mt-5">{{ $dataFormatada }}</p>

            <div class="mt-3 cert-signature">
                @inlinedImage(resource_path('images/certificados/assinatura.jpg'))
            </div>
        </div>

    </div>
    <div class="footer">
        @inlinedImage(resource_path('images/certificados/assets_antigos/rodape_atualizado.png'))
    </div>

    <div class="second-page">
        <div class="watermark">
            @inlinedImage(resource_path('images/certificados/marcadagua.png'))
        </div>

        <p class="field-label">Conteúdo Programático:</p>
        <p class="field-value">{{ $dadosDoc->content['conteudo_programatico'] ?? '' }}</p>

        <p class="field-label">Carga-horária:</p>
        <p class="field-value">{{ $dadosDoc->content['carga_horaria'] ?? '' }} horas</p>

        <p class="field-label">Instrutor(a):</p>
        <p class="field-value">{{ $dadosDoc->content['instrutor_nome'] ?? '' }}</p>

        <p class="field-label">Local de Realização:</p>
        <p class="field-value">{{ $localRealizacaoCertificado ?? '' }}</p>
    </div>
</body>

</html>
