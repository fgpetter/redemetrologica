<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtualizarInterlabRequest;
use App\Http\Requests\CriarInterlabRequest;
use App\Models\Interlab;
use App\Models\InterlabInscrito;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InterlabController extends Controller
{
    /**
     * Gera pagina de listagem de intelabs
     *
     **/
    public function index(Request $request): View
    {
        $sortDirection = in_array($request->input('order'), ['asc', 'desc'], true) ? $request->input('order') : 'asc';
        $sortField = in_array($request->input('orderBy'), ['nome', 'avaliacao', 'tipo'], true) ? $request->input('orderBy') : 'nome';
        $searchTerm = $request->input('buscanome');

        $interlabs = Interlab::when($searchTerm, function ($query) use ($searchTerm) {
            $query->where('nome', 'LIKE', "%{$searchTerm}%");
        })
            ->orderBy($sortField, $sortDirection)
            ->paginate(15)
            ->withQueryString();

        return view('painel.interlabs.index', ['interlabs' => $interlabs]);
    }

    /**
     * Gera pagina de listagem de laboratórios do intelabs
     *
     **/
    public function labindex(Request $request): View
    {
        return view('painel.interlabs.laboratorios');
    }

    /**
     * Tela de edição de interlab
     *
     **/
    public function insert(Interlab $interlab): View
    {
        $thumbs = [
            'AMOSTRAGEM' => 'PEP_AMOSTRAGEM.png',
            'ANALISE AMBIENTAL' => 'PEP_ANALISE_AMBIENTAL.png',
            'ANALISEESIDUOS TOXICOS' => 'PEP_ANALISEESIDUOS_TOXICOS.png',
            'ANALISE CARVAO' => 'PEP_ANALISE_CARVAO.png',
            'ANALISE MICROBIOLOGICAS AGUA' => 'PEP_ANALISE_MICROBIOLOGICAS_AGUA.png',
            'AVIARIA' => 'PEP_AVIARIA.png',
            'AZEITE' => 'PEP_AZEITE.png',
            'BEBIDAS' => 'PEP_BEBIDAS.png',
            'BIODIESEL' => 'PEP_BIODIESEL.png',
            'COMPOSTOS VOLATEIS' => 'PEP_COMPOSTOS_VOLATEIS.png',
            'ECO TOXICOLOGICO' => 'PEP_ECO_TOXICOLOGICO.png',
            'EQUINOS' => 'PEP_EQUINOS.png',
            'FEBRE AFTOSA' => 'PEP_FEBRE_AFTOSA.png',
            'HIDROBIOLOGIA' => 'PEP_HIDROBIOLOGIA.png',
            'INTERCOMPARACAO LAB' => 'PEP_INTERCOMPARACAO_LAB.png',
            'MECANICOS E METAL' => 'PEP_MECANICOS_E_METAL.png',
            'OLEOS E GRAXAS' => 'PEP_OLEOS_E_GRAXAS.png',
            'SEMENTES' => 'PEP_SEMENTES.png',
            'SOLOS' => 'PEP_SOLOS.png',
            'VEGETAL' => 'PEP_VEGETAL.png',
            'VETERINARIO' => 'PEP_VETERINARIO.png',
        ];

        return view('painel.interlabs.edit', ['interlab' => $interlab, 'thumbs' => $thumbs]);
    }

    /**
     * Adiciona interlab na base
     *
     **/
    public function create(CriarInterlabRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $interlab = Interlab::create($validated);

        if (! $interlab) {
            return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        return redirect()->route('interlab-index')->with('success', 'Interlab cadastrado com sucesso');
    }

    /**
     * Altera interlab
     *
     **/
    public function update(AtualizarInterlabRequest $request, Interlab $interlab): RedirectResponse
    {
        $validated = $request->validated();

        if ($interlab->tag !== $validated['tag']) {
            $existeInscritoComSenha = InterlabInscrito::whereHas('agendaInterlab', fn ($q) => $q->where('interlab_id', $interlab->id)
            )
                ->whereNotNull('tag_senha')
                ->exists();

            if ($existeInscritoComSenha) {
                return back()->withInput()->with(
                    'error',
                    'Não é possível alterar a TAG, pois existem inscritos com senha atribuída.'
                );
            }
        }

        $interlab->update($validated);

        return redirect()->route('interlab-index')->with('success', 'Interlab atualizado com sucesso');
    }

    /**
     * Remove interlab
     *
     * @param  User  $user
     **/
    public function delete(Interlab $interlab): RedirectResponse
    {

        $interlab->delete();

        return redirect()->route('interlab-index')->with('warning', 'Interlab removido');
    }
}
