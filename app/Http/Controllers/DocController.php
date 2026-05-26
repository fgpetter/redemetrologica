<?php

namespace App\Http\Controllers;

use App\Models\CursoInscrito;
use App\Models\DadosGeraDoc;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

class DocController extends Controller
{
    private const DEBUG_LOG_PATH = '/home/fgpetter/projects/sistema-rede/.cursor/debug-b97a9e.log';

    /**
     * Download de documento pelo link unico
     */
    public function download(string $link)
    {
        $dadosDoc = DadosGeraDoc::where('link', $link)->firstOrFail();

        $runId = 'download-flow-pre-fix';
        $fileName = $dadosDoc->file_name;
        $fileExists = (bool) ($fileName && Storage::exists($fileName));

        // #region agent log download-flow
        $this->debugLog($runId, 'FLOW_START', 'DocController.php:20', 'download_request', [
            'dados_doc_id' => $dadosDoc->id,
            'tipo' => $dadosDoc->tipo,
            'file_name' => $fileName,
            'file_exists' => $fileExists,
            'has_participante_id' => isset($dadosDoc->content['participante_id']),
        ]);
        // #endregion

        if ($dadosDoc->file_name && Storage::exists($dadosDoc->file_name)) {
            // #region agent log download-flow
            $this->debugLog($runId, 'FLOW_EXISTING_FILE', 'DocController.php:24', 'return_existing_pdf', [
                'storage_path' => $dadosDoc->storage_path,
            ]);
            // #endregion

            return response()->download(
                Storage::path($dadosDoc->file_name),
                basename($dadosDoc->file_name)
            );
        }

        if ($dadosDoc->tipo === 'tag_senha') {
            return $this->generateTagSenhaPdf($dadosDoc);
        }

        if ($dadosDoc->tipo === 'certificado') {
            return $this->generateCertificadoPdf($dadosDoc);
        }

        if ($dadosDoc->tipo === 'certificado_interlab') {
            return $this->generateCertificadoInterlabPdf($dadosDoc);
        }

        abort(500, 'Tipo de documento não suportado.');
    }

    /**
     * Geração do PDF para tag senha
     */
    private function generateTagSenhaPdf(DadosGeraDoc $dadosDoc)
    {
        $fileName = $dadosDoc->file_name;
        $path = $dadosDoc->storage_path;

        if (! Storage::exists(dirname($path))) {
            Storage::makeDirectory(dirname($path));
        }

        Pdf::view('certificados.tag-senha', [
            'dadosDoc' => $dadosDoc,
        ])->driver('dompdf')->save(Storage::path($path));

        $dadosDoc->update(['file_name' => $path]);

        return response()->download(Storage::path($path), $fileName);
    }

    /**
     * Geração do PDF para certificado
     */
    private function generateCertificadoPdf(DadosGeraDoc $dadosDoc)
    {
        $fileName = $dadosDoc->file_name;
        $path = $dadosDoc->storage_path;
        $runId = 'certificado-pre-fix';

        if (! Storage::exists(dirname($path))) {
            Storage::makeDirectory(dirname($path));
        }

        $participanteId = $dadosDoc->content['participante_id'] ?? throw new \Exception('Certificado sem identificação do participante.');

        $inscrito = CursoInscrito::with(['empresa', 'agendaCurso'])
            ->findOrFail($participanteId) ?? throw new \Exception('Inscrição sem agenda de curso associada.');

        $localRealizacaoCertificado = $this->resolverLocalRealizacaoCertificado($inscrito);

        // #region agent log H1
        $this->debugLog($runId, 'H1', 'DocController.php:82', 'generate_certificado_input', [
            'dados_doc_id' => $dadosDoc->id,
            'storage_path' => $path,
            'driver' => config('laravel-pdf.driver'),
            'participante_id' => $participanteId,
            'conteudo_programatico_chars' => mb_strlen((string) ($dadosDoc->content['conteudo_programatico'] ?? '')),
            'curso_nome_chars' => mb_strlen((string) ($dadosDoc->content['curso_nome'] ?? '')),
        ]);
        // #endregion

        $renderedHtml = view('certificados.certificado', [
            'dadosDoc' => $dadosDoc,
            'localRealizacaoCertificado' => $localRealizacaoCertificado,
        ])->render();

        // #region agent log H2
        $this->debugLog($runId, 'H2', 'DocController.php:95', 'certificado_html_metrics', [
            'html_length' => mb_strlen($renderedHtml),
            'has_min_height_100vh' => str_contains($renderedHtml, 'min-height: 100vh'),
            'has_page_break_before' => str_contains($renderedHtml, 'page-break-before: always'),
            'fixed_position_count' => substr_count($renderedHtml, 'position: fixed'),
            'watermark_count' => substr_count($renderedHtml, 'class="watermark"'),
        ]);
        // #endregion

        Pdf::view('certificados.certificado', [
            'dadosDoc' => $dadosDoc,
            'localRealizacaoCertificado' => $localRealizacaoCertificado,
        ])->format('a4')->landscape()->save(Storage::path($path));

        $generatedPdfRaw = @file_get_contents(Storage::path($path)) ?: '';

        $mediaBoxMatches = [];
        if ($generatedPdfRaw !== '') {
            // MediaBox usa pontos (points). A regex captura [llx lly urx ury]
            preg_match_all(
                '/\/MediaBox\s*\[\s*([0-9.]+)\s+([0-9.]+)\s+([0-9.]+)\s+([0-9.]+)\s*\]/',
                $generatedPdfRaw,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                $mediaBoxMatches[] = [
                    'llx' => (float) $match[1],
                    'lly' => (float) $match[2],
                    'urx' => (float) $match[3],
                    'ury' => (float) $match[4],
                ];
            }
        }

        // #region agent log H3
        $this->debugLog($runId, 'H3', 'DocController.php:112', 'certificado_pdf_generated', [
            'pdf_file_exists' => file_exists(Storage::path($path)),
            'pdf_size_bytes' => @filesize(Storage::path($path)) ?: null,
            'page_object_estimate' => preg_match_all('/\/Type\s*\/Page\b/', $generatedPdfRaw),
            'media_box_pages_count' => count($mediaBoxMatches),
            'media_box_first' => $mediaBoxMatches[0] ?? null,
            'media_box_second' => $mediaBoxMatches[1] ?? null,
        ]);
        // #endregion

        $dadosDoc->update(['file_name' => $path]);

        CursoInscrito::where('id', $participanteId)->update([
            'certificado_path' => $path,
        ]);

        return response()->download(Storage::path($path), $fileName);
    }

    /**
     * Local exibido no certificado: agenda IN-COMPANY usa o nome da empresa do inscrito;
     * demais tipos usam o nome da associação.
     */
    private function resolverLocalRealizacaoCertificado(CursoInscrito $inscrito): string
    {
        if ($inscrito->agendaCurso->tipo_agendamento === 'IN-COMPANY') {
            return $inscrito->empresa?->nome_razao ?? '';
        }

        return 'Associação Rede de Metrologia e Ensaios do RS';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function debugLog(string $runId, string $hypothesisId, string $location, string $message, array $data = []): void
    {
        try {
            file_put_contents(self::DEBUG_LOG_PATH, json_encode([
                'sessionId' => 'b97a9e',
                'runId' => $runId,
                'hypothesisId' => $hypothesisId,
                'location' => $location,
                'message' => $message,
                'data' => $data,
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL, FILE_APPEND);
        } catch (\Throwable) {
            // Ignora falha de log em modo debug.
        }
    }

    /**
     * Geração do PDF para certificado Interlab
     */
    private function generateCertificadoInterlabPdf(DadosGeraDoc $dadosDoc)
    {
        $fileName = $dadosDoc->file_name;
        $path = $dadosDoc->storage_path;

        if (! Storage::exists(dirname($path))) {
            Storage::makeDirectory(dirname($path));
        }

        // Buscar participante para gerar certificado
        $participante = \App\Models\InterlabInscrito::with(['laboratorio', 'agendaInterlab.interlab'])
            ->findOrFail($dadosDoc->content['participante_id']);

        Pdf::view('certificados.certificado-interlab', [
            'participante' => $participante,
        ])->format('a4')->landscape()->save(Storage::path($path));

        $dadosDoc->update(['file_name' => $path]);

        if (isset($dadosDoc->content['participante_id'])) {
            \App\Models\InterlabInscrito::where('id', $dadosDoc->content['participante_id'])->update([
                'certificado_path' => $path,
            ]);
        }

        return response()->download(Storage::path($path), $fileName);
    }
}
