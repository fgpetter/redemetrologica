<?php

namespace App\Http\Controllers;

use App\Models\Interlab;
use App\Models\InterlabInscrito;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InterlabController extends Controller
{
  /**
   * Gera pagina de listagem de intelabs
   *
   * @return View
   **/
   public function index(Request $request): View
{
    $sortDirection = $request->input('order', 'asc');
    $sortField = $request->input('orderBy', 'nome');
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
   * @return View
   **/
   public function labindex(Request $request): View
{  
    return view('painel.interlabs.labindex');
}

  /**
   * Tela de edição de interlab
   *
   * @param Interlab $interlab
   * @return View
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
      'VETERINARIO' => 'PEP_VETERINARIO.png'
    ];
    return view('painel.interlabs.insert', ['interlab' => $interlab, 'thumbs' => $thumbs]);
  }

  /**
   * Adiciona interlab na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $validated = $request->validate(
      [
        'nome' => ['required','string', 'max:190'],
        'descricao' => ['nullable', 'string'],
        'tipo' => ['nullable', 'string', 'in:BILATERAL,INTERLABORATORIAL'],
        'tag' => ['required', 'min:3', 'max:3'],
        'thumb' => ['nullable', 'string'],
        'observacoes' => ['nullable', 'string'],
      ],
      [
        'nome.required' => 'Preencha o campo Nome',
        'nome.string' => 'O campo aceita somente texto.',
        'nome.max' => 'O campo aceita no maximo 190 caracteres.',
        'descricao.string' => 'O campo aceita somente texto.',
        'tipo.in' => 'A opção selecionada é inválida',
        'tag.required' => 'Preencha o campo TAG',
        'tag.min' => 'O campo TAG deve ter no mínimo 3 caracteres.',
        'observacoes' => 'O campo aceita somente texto.'
      ]
    );

    $interlab = Interlab::create($validated);

    if (!$interlab) {
      return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('interlab-index')->with('success', 'Interlab cadastrado com sucesso');
  }

  /**
   * Altera interlab
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function update(Request $request, Interlab $interlab): RedirectResponse
  {
    $validated = $request->validate(
      [
      'nome' => ['required','string', 'max:190'],
      'descricao' => ['nullable', 'string'],
      'tipo' => ['nullable', 'string', 'in:BILATERAL,INTERLABORATORIAL'],
      'tag' => ['required', 'min:2', 'max:5'],
      'thumb' => ['nullable', 'string'],
      'observacoes' => ['nullable', 'string'],
      ],
      [
      'nome.required' => 'Preencha o campo Nome',
      'nome.string' => 'O campo aceita somente texto.',
      'nome.max' => 'O campo aceita no maximo 190 caracteres.',
      'descricao.string' => 'O campo aceita somente texto.',
      'tipo.in' => 'A opção selecionada é inválida',
      'tag.required' => 'Preencha o campo TAG',
      'tag.min' => 'O campo TAG deve ter no mínimo 2 caracteres.',
      'tag.max' => 'O campo TAG deve ter no máximo 5 caracteres.',
      'observacoes' => 'O campo aceita somente texto.'
      ]
    );

    if ($interlab->tag !== $validated['tag']) {

    $existeInscritoComSenha = InterlabInscrito::whereHas('agendaInterlab', fn ($q) =>
        $q->where('interlab_id', $interlab->id)
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
   * @param User $user
   * @return RedirectResponse
   **/
  public function delete(Interlab $interlab): RedirectResponse
  {
    // $tem_interlabs_agendados = AgendaCursos::where('interlab_id', $interlab->id)->first();
    // (!$tem_interlabs_agendados) ? $interlab->forceDelete() : $interlab->delete();

    $interlab->forceDelete();

    return redirect()->route('interlab-index')->with('warning', 'Interlab removido');
  }

}
