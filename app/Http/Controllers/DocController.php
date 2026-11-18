<?php

namespace App\Http\Controllers;

use App\Models\CursoInscrito;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocController extends Controller
{
    /**
     * Download de documento pelo link unico
     */
    public function download(string $link): BinaryFileResponse
    {
        $dadosDoc = DadosGeraDoc::where('link', $link)->firstOrFail();

        if (in_array($dadosDoc->tipo, DadosGeraDoc::TIPOS_CARTA_SENHA, true)) {
            return $this->downloadCartaSenha($dadosDoc);
        }

        if ($dadosDoc->file_name && Storage::exists($dadosDoc->file_name)) {
            return response()->download(
                Storage::path($dadosDoc->file_name),
                basename($dadosDoc->file_name)
            );
        }

        if ($dadosDoc->tipo === 'certificado') {
            return $this->generateCertificadoPdf($dadosDoc);
        }

        if ($dadosDoc->tipo === 'certificado_interlab') {
            return $this->generateCertificadoInterlabPdf($dadosDoc);
        }

        abort(500, 'Tipo de documento não suportado.');
    }

    private function downloadCartaSenha(DadosGeraDoc $dadosDoc): BinaryFileResponse
    {
        return DB::transaction(function () use ($dadosDoc): BinaryFileResponse {
            $dadosDoc = DadosGeraDoc::query()->lockForUpdate()->findOrFail($dadosDoc->getKey());
            $disco = Storage::disk('public');
            $caminhoSalvo = $dadosDoc->getRawOriginal('file_name');
            $diretorio = DadosGeraDoc::DIRETORIO_CARTAS_SENHA;

            if ($caminhoSalvo !== null
                && DadosGeraDoc::normalizarCaminhoCartaSenha($caminhoSalvo) === $caminhoSalvo
                && $caminhoSalvo === $dadosDoc->storage_path
                && $disco->fileExists($caminhoSalvo)) {
                return response()->download($disco->path($caminhoSalvo), basename($caminhoSalvo));
            }

            $caminho = $dadosDoc->storage_path;
            $disco->makeDirectory($diretorio);

            $view = $dadosDoc->tipo === 'tag_senha'
                ? 'certificados.tag-senha'
                : 'certificados.tag-senha-analista';

            Pdf::view($view, ['dadosDoc' => $dadosDoc])->driver('dompdf')->save($disco->path($caminho));

            $dadosDoc->update(['file_name' => $caminho]);

            return response()->download($disco->path($caminho), basename($caminho));
        });
    }

    /**
     * Geração do PDF para certificado
     */
    private function generateCertificadoPdf(DadosGeraDoc $dadosDoc): BinaryFileResponse
    {
        $fileName = $dadosDoc->file_name;
        $path = $dadosDoc->storage_path;

        if (! Storage::exists(dirname($path))) {
            Storage::makeDirectory(dirname($path));
        }

        $participanteId = $dadosDoc->content['participante_id'] ?? throw new \Exception('Certificado sem identificação do participante.');

        $inscrito = CursoInscrito::with(['empresa', 'agendaCurso'])
            ->findOrFail($participanteId) ?? throw new \Exception('Inscrição sem agenda de curso associada.');

        $localRealizacaoCertificado = $this->resolverLocalRealizacaoCertificado($inscrito);

        Pdf::view('certificados.certificado', [
            'dadosDoc' => $dadosDoc,
            'localRealizacaoCertificado' => $localRealizacaoCertificado,
        ])->format('a4')->landscape()->save(Storage::path($path));

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
     * Geração do PDF para certificado Interlab
     */
    private function generateCertificadoInterlabPdf(DadosGeraDoc $dadosDoc): BinaryFileResponse
    {
        $fileName = $dadosDoc->file_name;
        $path = $dadosDoc->storage_path;

        if (! Storage::exists(dirname($path))) {
            Storage::makeDirectory(dirname($path));
        }

        // Buscar participante para gerar certificado
        $participante = InterlabInscrito::with(['laboratorio', 'agendaInterlab.interlab'])
            ->findOrFail($dadosDoc->content['participante_id']);

        Pdf::view('certificados.certificado-interlab', [
            'participante' => $participante,
        ])->format('a4')->landscape()->save(Storage::path($path));

        $dadosDoc->update(['file_name' => $path]);

        if (isset($dadosDoc->content['participante_id'])) {
            InterlabInscrito::where('id', $dadosDoc->content['participante_id'])->update([
                'certificado_path' => $path,
            ]);
        }

        return response()->download(Storage::path($path), $fileName);
    }
}
