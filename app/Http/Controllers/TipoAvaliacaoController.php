<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\TipoAvaliacao;

class TipoAvaliacaoController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request): View
    {
        $sortDirection = $request->input('order', 'asc');
        $sortField     = $request->input('orderBy', 'descricao');
        $searchTerm    = $request->input('buscadecricao');

        $avaliacoes = TipoAvaliacao::when($searchTerm, function ($query) use ($searchTerm) {
                $query->where('descricao', 'LIKE', "%{$searchTerm}%");
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(15)
            ->withQueryString();

        return view('painel.tipos-avaliacao.index', ['avaliacoes' => $avaliacoes]);
    }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',

      ]
    );


    $tipo_avaliacao = TipoAvaliacao::create($validated);

    if (!$tipo_avaliacao) {
      return redirect()->back()
        ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('tipo-avaliacao-index')
      ->with('success', 'Tipo Avaliação cadastrado com sucesso');
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, TipoAvaliacao $tipoAvaliacao)
  {
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',
      ]
    );

    $tipoAvaliacao->update($validated);

    return redirect()->route('tipo-avaliacao-index')
      ->with('success', 'Tipo Avaliação cadastrado com sucesso');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(TipoAvaliacao $tipoAvaliacao)
  {
    $tipoAvaliacao->delete();

    return redirect()->route('tipo-avaliacao-index')
      ->with('warning', 'Tipo Avaliação removido com sucesso');
  }
}
