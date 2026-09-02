<?php

namespace App\Actions\Avaliacoes;

use App\Models\AgendaAvaliacao;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function Spatie\LaravelPdf\Support\pdf;

class GerarPdfMediaAvaliacoesAction
{
    /**
     * Gera o PDF do relatório de médias de avaliações (DOMPDF síncrono).
     *
     * @param  string  $dataIni  Filtro data inicial (Y-m-d) ou vazio
     * @param  string  $dataFim  Filtro data final (Y-m-d) ou vazio
     * @param  string  $search  Busca por laboratório / laboratório interno
     */
    public function execute(string $dataIni = '', string $dataFim = '', string $search = ''): BinaryFileResponse
    {
        $avaliacoes = $this->getQuery($dataIni, $dataFim, $search)->get();

        $grupos = $avaliacoes->groupBy(function (AgendaAvaliacao $avaliacao) {
            return $avaliacao->data_inicio
                ? Carbon::parse($avaliacao->data_inicio)->format('m/Y')
                : '';
        });

        $relativePath = 'tmp/media-avaliacoes/'.Str::uuid().'.pdf';

        if (! Storage::exists('tmp/media-avaliacoes')) {
            Storage::makeDirectory('tmp/media-avaliacoes');
        }

        pdf()
            ->view('pdf.avaliacoes.media', [
                'grupos' => $grupos,
                'dataIni' => $dataIni,
                'dataFim' => $dataFim,
            ])
            ->driver('dompdf')
            ->format('a4')
            ->save(Storage::path($relativePath));

        return response()
            ->download(Storage::path($relativePath), 'media-avaliacoes.pdf')
            ->deleteFileAfterSend();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<AgendaAvaliacao>
     */
    protected function getQuery(string $dataIni, string $dataFim, string $search)
    {
        return AgendaAvaliacao::query()
            ->with(['laboratorio', 'laboratorioInterno', 'pesquisa'])
            ->when($dataIni, fn ($query, $date) => $query->where('data_inicio', '>=', $date))
            ->when($dataFim, fn ($query, $date) => $query->where('data_inicio', '<=', $date))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('laboratorio', fn ($q) => $q->where('nome_laboratorio', 'like', "%{$search}%"))
                        ->orWhereHas('laboratorioInterno', fn ($q) => $q->where('nome', 'like', "%{$search}%"));
                });
            })
            ->orderBy('data_inicio');
    }
}
