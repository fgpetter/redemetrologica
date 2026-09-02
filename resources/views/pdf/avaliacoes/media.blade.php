<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Média de Avaliações</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 12mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 10pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .logo-cell {
            width: 28%;
            text-align: left;
        }

        .logo-cell img {
            width: 140px;
            height: auto;
        }

        .title-cell {
            width: 72%;
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
        }

        .filters {
            margin-top: 18px;
            margin-bottom: 8px;
            font-size: 10pt;
        }

        .filters td {
            border: none;
            padding: 2px 0;
        }

        .separator {
            border: none;
            border-top: 1px solid #000;
            margin: 8px 0 12px;
        }

        .report-table th {
            text-align: left;
            font-weight: bold;
            padding: 4px 6px;
            border-bottom: 1px solid #000;
        }

        .report-table th.col-media,
        .report-table td.col-media {
            text-align: right;
        }

        .report-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .month-footer td {
            padding-top: 10px;
            padding-bottom: 14px;
            font-weight: bold;
        }

        .month-footer .media-label {
            text-align: right;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @inlinedImage(resource_path('images/certificados/LOGO_REDE_COLOR.png'))
            </td>
            <td class="title-cell">Média de Avaliações</td>
        </tr>
    </table>

    <table class="filters">
        <tr>
            <td>
                Data Inicial:
                {{ $dataIni ? \Carbon\Carbon::parse($dataIni)->format('d/m/Y') : '' }}
            </td>
            <td>
                Data Final:
                {{ $dataFim ? \Carbon\Carbon::parse($dataFim)->format('d/m/Y') : '' }}
            </td>
        </tr>
    </table>

    <hr class="separator">

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 14%;">Data Inicio</th>
                <th style="width: 38%;">Laboratório</th>
                <th style="width: 28%;">Laboratório Interno</th>
                <th class="col-media" style="width: 20%;">Média Pesquisa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($grupos as $mes => $itens)
                @php
                    $respondidas = $itens->filter(fn ($avaliacao) => $avaliacao->pesquisa?->preenchido_em !== null && $avaliacao->med_pesquisa !== null);
                    $mediaGrupo = $respondidas->isEmpty() ? '---' : formataValorBr(round($respondidas->avg('med_pesquisa'), 2));
                @endphp

                @foreach ($itens as $avaliacao)
                    <tr>
                        <td>{{ $avaliacao->data_inicio ? \Carbon\Carbon::parse($avaliacao->data_inicio)->format('d/m/Y') : '' }}</td>
                        <td>{{ $avaliacao->laboratorio->nome_laboratorio ?? '' }}</td>
                        <td>{{ $avaliacao->laboratorioInterno->nome ?? '' }}</td>
                        <td class="col-media">
                            @if ($avaliacao->pesquisa?->preenchido_em === null)
                                Não Respondida
                            @else
                                {{ formataValorBr($avaliacao->med_pesquisa) }}
                            @endif
                        </td>
                    </tr>
                @endforeach

                <tr class="month-footer">
                    <td colspan="3">Mês: {{ $mes }}</td>
                    <td class="media-label">Média: {{ $mediaGrupo }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding-top: 20px;">Não há avaliações</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
