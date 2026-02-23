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

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .certificate-container {
            padding-bottom: 40mm; /* ajuste conforme a altura do seu rodapé */
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            background-image: url("{{ resource_path('images/certificados/marcadagua.png') }}");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
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
    </style>
</head>

<body class="text-center bg-white">
    <div class="certificate-container p-2">
        <div style="position: fixed; left: 0; top: 50%; transform: translateY(-50%); writing-mode: vertical-lr; z-index: 999;">
            FR74 - rev03
        </div>
        <div class="text-center mb-3">
            <img src="{{ resource_path('images/certificados/LOGO_REDE_COLOR.png') }}" alt="Logo" style="width: 300px;">
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

            <div class="mt-4">
                <img src="{{ resource_path('images/certificados/assinatura_joao_redemetrologica.png') }}" style="height: 140px;">
            </div>
        </div>
        
    </div>
    <div class="footer">
        <img src="{{ resource_path('images/certificados/rodape_certificado_pep_redemetrologica.png') }}">
    </div>
</body>

</html>
