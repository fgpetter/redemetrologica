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
            width: 100%;
            display: block;
        }

        .cert-logo img {
            width: 300px;
        }

        .cert-signature img {
            height: 140px;
        }
    </style>
</head>

<body class="text-center bg-white">
    <div class="watermark">
        @inlinedImage(resource_path('images/certificados/marcadagua.png'))
    </div>

    <div class="certificate-container p-2">
        <div style="position: fixed; left: 0; top: 50%; transform: translateY(-50%); writing-mode: vertical-lr; z-index: 999;">
            FR74 - rev03
        </div>
        <div class="text-center mb-3 cert-logo">
            @inlinedImage(resource_path('images/certificados/LOGO_REDE_COLOR.png'))
        </div>

        <div class="mt-2">
            <h1 class="display-4 font-weight-bold">CERTIFICADO</h1>

            <p class="h3">Associação Rede de Metrologia e Ensaios do RS certifica que</p>

            <p class="h4 font-weight-bold my-3">{{ $participante->laboratorio->nome }}</p>

            <p class="h3">Participou do</p>

            <p class="h4 font-weight-bold mt-3 mb-3">{{ $participante->agendaInterlab->interlab->nome }} - {{ $participante->agendaInterlab->ano_referencia }}</p>
            @php
                setlocale(LC_TIME, 'pt_BR.UTF-8');
                $data = \Carbon\Carbon::now();
                $dataFormatada =
                    'Porto Alegre, ' .
                    $data->format('d') .
                    ' de ' .
                    ucfirst($data->locale('pt_BR')->getTranslatedMonthName()) .
                    ' de ' .
                    $data->format('Y');
            @endphp

            <p class="h6 mt-5">{{ $dataFormatada }}</p>

            <div class="mt-4 cert-signature">
                @inlinedImage(resource_path('images/certificados/assinatura_joao_redemetrologica.png'))
            </div>
        </div>

    </div>
    <div class="footer">
        @inlinedImage(resource_path('images/certificados/rodape_certificado_pep_redemetrologica.png'))
    </div>
</body>

</html>
