<?php

namespace App\Livewire\PainelCliente;

use App\Models\DadosGeraDoc;
use Livewire\Component;

class MeusPeps extends Component
{
    public function render()
    {
        $pessoa = auth()->user()->pessoa;
        $interlabs = $pessoa
            ?->interlabs()
            ->with([
                'agendaInterlab.interlab',
                'agendaInterlab.materiais',
                'analistas',
                'laboratorio.endereco',
                'empresa',
            ])
            ->latest('id')
            ->get() ?? collect();
        $documentos = DadosGeraDoc::query()
            ->whereIn('tipo', ['tag_senha', 'tag_senha_analista', 'certificado_interlab'])
            ->whereIn('content->participante_id', $interlabs->pluck('id'))
            ->latest('id')
            ->get()
            ->groupBy(fn (DadosGeraDoc $doc) => $doc->content['participante_id']);

        $documentosPorInscricao = $interlabs->mapWithKeys(function ($inscrito) use ($documentos) {
            $docs = $documentos->get($inscrito->id, collect());
            $url = fn ($doc) => $doc ? route('dados-doc.download', $doc->link) : null;

            return [$inscrito->id => [
                'carta' => $url($docs->firstWhere('tipo', 'tag_senha')),
                'certificado' => $inscrito->is_certificado_emitido ? $url($docs->firstWhere('tipo', 'certificado_interlab')) : null,
                'analistas' => $inscrito->analistas->map(fn ($analista) => [
                    'nome' => $analista->nome,
                    'carta' => $url($docs->first(fn ($doc) => $doc->tipo === 'tag_senha_analista' && (string) ($doc->content['analista_id'] ?? '') === (string) $analista->id)),
                    'certificado' => null,
                ])->all(),
            ]];
        })->all();

        return view('livewire.painel-cliente.meus-peps', compact('interlabs', 'documentosPorInscricao'));
    }
}
