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

        abort(500, 'Tipo de documento não suportado.');
    }

    /**
     * Geração do PDF para tag senha
     */
    private function generateTagSenhaPdf(DadosGeraDoc $dadosDoc)
    {
        $labNameSlug = Str::slug($dadosDoc->content['laboratorio_nome']);
        $fileName = 'tag_senha_' . $labNameSlug . '_' . $dadosDoc->link . '.pdf';
        $path = 'public/docs/senhas/' . $fileName;

        if (!Storage::exists('public/docs/senhas')) {
            Storage::makeDirectory('public/docs/senhas');
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
        $participanteNameSlug = Str::slug($dadosDoc->content['participante_nome']);
        $fileName = 'certificado_' . $participanteNameSlug . '_' . $dadosDoc->link . '.pdf';
        $path = 'public/docs/certificados/' . $fileName;

        if (!Storage::exists('public/docs/certificados')) {
            Storage::makeDirectory('public/docs/certificados');
        }

        Pdf::view('certificados.certificado', [
            'dadosDoc' => $dadosDoc,
        ])->format('a4')->landscape()->save(Storage::path($path));

        $dadosDoc->update(['file_name' => $path]);
       
        return response()->download(Storage::path($path), $fileName);
    }
}
