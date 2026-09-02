@php
    $perguntas = [
        1 => 'Postura dos avaliadores',
        2 => 'Clareza das informações contidas no relatório de avaliação',
        3 => 'Pontualidade dos avaliadores',
        4 => 'Avaliadores foram fiéis aos critérios de avaliação previamente estabelecidos pela Rede Metrológica',
        5 => 'Clareza das informações colocadas na reunião de abertura e encerramento de avaliação',
        6 => 'O atendimento às expectativas do laboratório referente à avaliação realizada',
        7 => 'A contribuição da Rede Metrológica para a melhoria do Sistema da Qualidade do laboratório',
    ];
    $avaliadoresUnicos = $avaliacao->areas->unique('avaliador_id');
    $comentariosSalvos = collect($pesquisa->comentarios_avaliadores ?? [])->keyBy('avaliador_id');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pesquisa de Satisfação</title>
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

        .meta {
            margin-top: 18px;
            margin-bottom: 8px;
            font-size: 10pt;
        }

        .meta td {
            border: none;
            padding: 2px 0;
        }

        .separator {
            border: none;
            border-top: 1px solid #000;
            margin: 8px 0 12px;
        }

        .info-table th,
        .info-table td,
        .notas-table th,
        .notas-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        .info-table th,
        .notas-table th {
            text-align: left;
            font-weight: bold;
            background: #f0f0f0;
        }

        .notas-table th.nota,
        .notas-table td.nota {
            text-align: center;
            width: 22px;
        }

        .notas-table td.selected {
            background: #cfcfcf;
            font-weight: bold;
        }

        .section {
            margin-top: 14px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .answer {
            margin-bottom: 12px;
            white-space: pre-wrap;
        }

        .answer-label {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .footer-meta {
            margin-top: 16px;
        }

        .footer-meta td {
            border: none;
            padding: 3px 0;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @inlinedImage(resource_path('images/certificados/LOGO_REDE_COLOR.png'))
            </td>
            <td class="title-cell">Pesquisa de Satisfação</td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td>
                Laboratório Avaliado:
                {{ $avaliacao->laboratorio->nome_laboratorio ?? '' }}
                -
                {{ $avaliacao->laboratorio->pessoa->cpf_cnpj ?? '' }}
            </td>
        </tr>
        <tr>
            <td>
                Data da Auditoria:
                {{ $avaliacao->data_inicio ? \Carbon\Carbon::parse($avaliacao->data_inicio)->format('d/m/Y') : '' }}
                a
                {{ $avaliacao->data_fim ? \Carbon\Carbon::parse($avaliacao->data_fim)->format('d/m/Y') : '' }}
            </td>
        </tr>
    </table>

    <hr class="separator">

    <table class="info-table">
        <thead>
            <tr>
                <th style="width: 50%;">Avaliador</th>
                <th style="width: 50%;">Área de Atuação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($avaliacao->areas as $area)
                <tr>
                    <td>{{ $area->avaliador?->pessoa?->nome_razao }}</td>
                    <td>{{ $area->areaAtuacao?->descricao }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">—</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="section">Abaixo, dê uma nota de 0 a 10 para cada um dos itens:</p>

    <table class="notas-table">
        <thead>
            <tr>
                <th></th>
                @for ($n = 0; $n <= 10; $n++)
                    <th class="nota">{{ $n }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($perguntas as $indice => $texto)
                @php
                    $notaSalva = $pesquisa->{'nota_'.$indice};
                @endphp
                <tr>
                    <td>{{ $indice }}. {{ $texto }}</td>
                    @for ($n = 0; $n <= 10; $n++)
                        <td class="nota {{ (string) $notaSalva === (string) $n ? 'selected' : '' }}">
                            {{ (string) $notaSalva === (string) $n ? 'X' : '' }}
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="answer">
        <div class="answer-label">
            Os critérios utilizados pelos avaliadores na presente avaliação e os utilizados pelos avaliadores na avaliação anterior foram harmoniosos/compatíveis? Caso negativo, favor justificar sua resposta.
        </div>
        {{ $pesquisa->criterios_harmoniosos }}
    </div>

    <div class="answer">
        <div class="answer-label">
            Houve pontos de divergências no final da avaliação? Se “sim”, quais?
        </div>
        {{ $pesquisa->divergencias }}
    </div>

    <div class="answer">
        <div class="answer-label">
            Pontos de melhoria na sistemática de avaliação / Reclamações:
        </div>
        {{ $pesquisa->pontos_melhoria }}
    </div>

    @foreach ($avaliadoresUnicos as $area)
        <div class="answer">
            <div class="answer-label">{{ mb_strtoupper($area->avaliador?->pessoa?->nome_razao) }}:</div>
            {{ $comentariosSalvos[$area->avaliador_id]['comentario'] ?? '' }}
        </div>
    @endforeach

    <table class="footer-meta">
        <tr>
            <td>Responsável pelas Informações: {{ $pesquisa->responsavel }}</td>
        </tr>
        <tr>
            <td>
                Data Preenchimento:
                {{ $pesquisa->preenchido_em?->format('d/m/Y H:i:s') }}
            </td>
        </tr>
        @if ($avaliacao->med_pesquisa !== null)
            <tr>
                <td>Média: {{ formataValorBr($avaliacao->med_pesquisa) }}</td>
            </tr>
        @endif
    </table>
</body>
</html>
