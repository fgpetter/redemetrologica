<?php

namespace App\Actions\Avaliacoes;

use App\Models\AgendaAvaliacao;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function Spatie\LaravelPdf\Support\pdf;

class GerarPdfPesquisaSatisfacaoAction
{
    /**
     * Gera o PDF da pesquisa de satisfação da avaliação (DOMPDF síncrono).
     */
    public function execute(AgendaAvaliacao $avaliacao): BinaryFileResponse
    {
        $avaliacao->loadMissing([
            'laboratorio.pessoa',
            'areas.avaliador.pessoa',
            'areas.areaAtuacao',
            'pesquisa',
        ]);

        if (! $avaliacao->pesquisa) {
            abort(404);
        }

        $relativePath = 'tmp/pesquisa-satisfacao/'.Str::uuid().'.pdf';

        if (! Storage::exists('tmp/pesquisa-satisfacao')) {
            Storage::makeDirectory('tmp/pesquisa-satisfacao');
        }

        pdf()
            ->view('pdf.avaliacoes.pesquisa', [
                'avaliacao' => $avaliacao,
                'pesquisa' => $avaliacao->pesquisa,
            ])
            ->driver('dompdf')
            ->format('a4')
            ->save(Storage::path($relativePath));

        return response()
            ->download(Storage::path($relativePath), 'pesquisa-satisfacao.pdf')
            ->deleteFileAfterSend();
    }
}
