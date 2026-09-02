<?php

namespace App\Http\Controllers;

use App\Actions\Avaliacoes\ResponderPesquisaSatisfacaoAction;
use App\Http\Requests\StorePesquisaSatisfacaoRequest;
use App\Models\AgendaAvaliacao;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PesquisaSatisfacaoController extends Controller
{
    /**
     * Exibe o formulário público da pesquisa de satisfação.
     */
    public function show(AgendaAvaliacao $avaliacao): View
    {
        $avaliacao->load(['laboratorio.pessoa', 'areas.avaliador.pessoa', 'areas.areaAtuacao', 'pesquisa']);

        return view('site.pages.pesquisa-satisfacao', [
            'avaliacao' => $avaliacao,
            'estado' => $this->estado($avaliacao),
        ]);
    }

    /**
     * Recebe a resposta pública da pesquisa de satisfação.
     */
    public function submit(StorePesquisaSatisfacaoRequest $request, AgendaAvaliacao $avaliacao): RedirectResponse
    {
        $avaliacao->load(['laboratorio.pessoa', 'areas.avaliador.pessoa', 'pesquisa']);

        if ($this->estado($avaliacao) !== 'formulario') {
            return redirect()->route('pesquisa-satisfacao.show', $avaliacao);
        }

        app(ResponderPesquisaSatisfacaoAction::class)->execute($avaliacao, $request->validated());

        return redirect()->route('pesquisa-satisfacao.show', $avaliacao)
            ->with('success', 'Pesquisa de satisfação enviada. Obrigado.');
    }

    private function estado(AgendaAvaliacao $avaliacao): string
    {
        if ((int) $avaliacao->carta_reconhecimento !== 1 || $avaliacao->pesquisa === null) {
            return 'indisponivel';
        }

        if ($avaliacao->pesquisa->preenchido_em !== null) {
            return 'respondida';
        }

        return 'formulario';
    }
}
