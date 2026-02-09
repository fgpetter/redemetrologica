<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DadosGeraDoc;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocController extends Controller
{
    /**
     * Download de documento pelo link unico
     */
    public function download(string $link)
    {
        $dadosDoc = DadosGeraDoc::where('link', $link)->firstOrFail();

        if ($dadosDoc->file_name && Storage::exists($dadosDoc->file_name)) {
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

        if (!Storage::exists(dirname($path))) {
            Storage::makeDirectory(dirname($path));
        }

        Pdf::view('certificados.tag-senha', [
            'dadosDoc' => $dadosDoc,
        ])->save(Storage::path($path));

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

        if (!Storage::exists(dirname($path))) {
            Storage::makeDirectory(dirname($path));
        }

        Pdf::view('certificados.certificado', [
            'dadosDoc' => $dadosDoc,
        ])->format('a4')->landscape()->save(Storage::path($path));

        $dadosDoc->update(['file_name' => $path]);

        if (isset($dadosDoc->content['participante_id'])) {
            \App\Models\CursoInscrito::where('id', $dadosDoc->content['participante_id'])->update([
                'certificado_path' => $path
            ]);
        }
       
        return response()->download(Storage::path($path), $fileName);
    }

    /**
     * Geração do PDF para certificado Interlab
     */
    private function generateCertificadoInterlabPdf(DadosGeraDoc $dadosDoc)
    {
        $fileName = $dadosDoc->file_name;
        $path = $dadosDoc->storage_path;

        if (!Storage::exists(dirname($path))) {
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
                'certificado_path' => $path
            ]);
        }
       
        return response()->download(Storage::path($path), $fileName);
    }
}
