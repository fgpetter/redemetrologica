<?php

namespace App\Exports;

use App\Models\InterlabDespesaLancamento;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Fr135Export
{
    public function download(int $ano, ?int $agendaId = null, ?string $nomePep = null): StreamedResponse
    {
        $compras = InterlabDespesaLancamento::query()
            ->whereHas('agendaInterlab', fn ($query) => $query->where('ano_referencia', $ano))
            ->when($agendaId, fn ($query) => $query->where('agenda_interlab_id', $agendaId))
            ->with(['agendaInterlab.interlab', 'fornecedor.pessoa', 'itens', 'avaliacao.avaliador'])
            ->orderBy('id')->get();

        $incluidas = $compras->filter(fn ($compra) => $compra->fornecedor && filled($compra->tipo) && Str::trim($compra->especificacao ?? '') !== '');
        $grupos = $incluidas->groupBy(fn ($compra) => $compra->fornecedor_id.'|'.$compra->tipo.'|'.Str::lower(Str::squish($compra->especificacao)));
        $planilha = new Spreadsheet;
        $aba = $planilha->getActiveSheet();
        $aba->setTitle('FR 135 PEP');
        foreach (['E1:K1', 'E2:K2', 'L1:M1', 'L2:M2', 'F4:I4', 'J4:K4', 'G7:J7'] as $merge) {
            $aba->mergeCells($merge);
        }
        foreach (['B', 'C', 'D', 'E', 'F', 'K', 'L', 'M'] as $coluna) {
            $aba->mergeCells("{$coluna}7:{$coluna}8");
        }
        $valores = [
            'E1' => 'PROVEDORES EXTERNOS', 'E2' => 'Formulário',
            'L1' => 'FR-135 / Rev 03', 'L2' => '01/08/2025',
            'C4' => 'Período avaliado:', 'D4' => (string) $ano,
            'F4' => 'Verificação anual da completude realizado por:',
            'L4' => 'Critério de aceitação:', 'M4' => 'Aprovado = NF > 7',
            'C5' => 'Data da última atualização:', 'M5' => 'Reprovado = NF ≤ 7',
            'B7' => 'ID', 'C7' => 'Interlaboratorial', 'D7' => 'Produto ou serviço',
            'E7' => 'Fornecedor', 'F7' => 'Especificação', 'G7' => 'Avaliação',
            'G8' => 'custo', 'H8' => 'qualidade', 'I8' => 'tempo', 'J8' => 'NF',
            'K7' => 'Status', 'L7' => 'OBS', 'M7' => 'Responsável pela avaliação',
        ];
        foreach ($valores as $celula => $valor) {
            $aba->setCellValueExplicit($celula, $valor, DataType::TYPE_STRING);
        }
        $datas = $incluidas->flatMap(fn ($compra) => collect([$compra->updated_at, $compra->avaliacao?->updated_at])->merge($compra->itens->pluck('updated_at')))->filter();
        if ($datas->isNotEmpty()) {
            $aba->setCellValueExplicit('D5', $datas->max()->format('d/m/Y'), DataType::TYPE_STRING);
        }
        $aba->mergeCells('B6:M6');
        $aba->setCellValueExplicit('B6', 'Compras excluídas por dados incompletos: '.($compras->count() - $incluidas->count()), DataType::TYPE_STRING);
        $linha = 9;
        $numeroGrupo = 0;
        foreach ($grupos as $grupo) {
            $linha = $this->preencherGrupo($aba, $linha, ++$numeroGrupo, $grupo);
        }
        if ($grupos->isEmpty()) {
            $aba->mergeCells('B9:M9');
            $aba->setCellValueExplicit('B9', 'Nenhuma compra encontrada para o período.', DataType::TYPE_STRING);
        }
        $ultima = max(9, $linha - 1);
        $aba->getStyle("B7:M{$ultima}")->getBorders()->getAllBorders()->setBorderStyle('thin');
        $aba->getStyle("B1:M{$ultima}")->getAlignment()->setWrapText(true)->setVertical('center');
        $aba->getStyle('B7:M8')->getFont()->setBold(true);
        $aba->getRowDimension(7)->setRowHeight(28);
        $aba->getRowDimension(8)->setRowHeight(24);
        $aba->getStyle('E1')->getFont()->setBold(true)->setSize(16);
        foreach (['B' => 8, 'C' => 24, 'D' => 17, 'E' => 25, 'F' => 28, 'G' => 10, 'H' => 10, 'I' => 10, 'J' => 10, 'K' => 14, 'L' => 55, 'M' => 25] as $coluna => $largura) {
            $aba->getColumnDimension($coluna)->setWidth($largura);
        }
        $logo = new Drawing;
        $logo->setPath(resource_path('images/site/LOGO_REDE_COLOR.png'));
        $logo->setCoordinates('B1');
        $logo->setHeight(50);
        $logo->setWorksheet($aba);
        $aba->getPageSetup()->setOrientation('landscape')->setPrintArea("B1:M{$ultima}")->setRowsToRepeatAtTopByStartAndEnd(1, 8)->setFitToWidth(1)->setFitToHeight(0);

        $nomeArquivo = "FR-135-{$ano}.xlsx";
        if ($agendaId !== null) {
            $pep = Str::upper(Str::slug($nomePep ?? '')) ?: 'PEP';
            $nomeArquivo = "FR135-{$pep}-{$ano}.xlsx";
        }

        return response()->streamDownload(function () use ($planilha) {
            (new Xlsx($planilha))->save('php://output');
            $planilha->disconnectWorksheets();
        }, $nomeArquivo, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function preencherGrupo(Worksheet $aba, int $linha, int $numeroGrupo, Collection $grupo): int
    {
        $primeira = $grupo->first();
        $completas = $grupo->pluck('avaliacao')->filter(fn ($a) => $a && $a->custo !== null && $a->qualidade !== null && $a->tempo !== null);
        $quantidade = $completas->count();
        $pendentes = $grupo->count() - $quantidade;
        $soma = $completas->sum(fn ($a) => $a->custo + $a->qualidade + $a->tempo);
        $observacoes = $grupo->flatMap(function ($compra) {
            $data = $compra->data_compra?->format('d/m/Y');

            return $compra->itens->map(fn ($item) => implode(' - ', array_filter([
                $item->material_servico,
                ($item->data_compra || $data) ? 'Compra: '.($data ?? $item->data_compra?->format('d/m/Y')) : null,
                filled($item->lote) ? 'Lote: '.$item->lote : null,
                $item->validade ? 'Validade: '.$item->validade->format('d/m/Y') : null,
                filled($item->nota_fiscal) ? 'Nota fiscal: '.$item->nota_fiscal : null,
            ], fn ($valor) => filled($valor))));
        })->filter(fn ($observacao) => filled($observacao))->values();
        if ($pendentes) {
            $observacoes->push("Compras sem avaliação completa: {$pendentes}");
        }
        $textos = [
            'B' => (string) $numeroGrupo,
            'C' => $grupo->map(fn ($c) => $c->agendaInterlab?->interlab?->nome)->filter()->unique()->implode(', '),
            'D' => $primeira->tipo === 'servico' ? 'Serviço' : ucfirst($primeira->tipo),
            'E' => $primeira->fornecedor->pessoa?->nome_razao ?? 'Não informado',
            'F' => $primeira->especificacao,
            'K' => $pendentes ? 'Pendente' : ($soma > 21 * $quantidade ? 'Aprovado' : 'Reprovado'),
            'M' => $completas->map(fn ($a) => $a->avaliador?->name ?: 'Não informado')->unique()->implode(', ') ?: 'Não informado',
        ];
        foreach ($textos as $coluna => $texto) {
            $aba->setCellValueExplicit($coluna.$linha, $texto, DataType::TYPE_STRING);
        }
        foreach ($observacoes as $indice => $observacao) {
            $aba->setCellValueExplicit('L'.($linha + $indice), $observacao, DataType::TYPE_STRING);
        }
        if ($quantidade) {
            foreach (['G' => 'custo', 'H' => 'qualidade', 'I' => 'tempo'] as $coluna => $criterio) {
                $aba->setCellValue($coluna.$linha, $completas->sum($criterio) / $quantidade);
            }
            $aba->setCellValue('J'.$linha, $soma / (3 * $quantidade));
            $aba->getStyle("G{$linha}:J{$linha}")->getNumberFormat()->setFormatCode('0.00');
        }

        return $linha + max(1, $observacoes->count());
    }
}
