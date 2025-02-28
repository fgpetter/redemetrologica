<?php

namespace App\Http\Controllers;

use App\Models\Parametro;
use Illuminate\Http\Request;

class ParametrosController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {

    return view('painel.parametros.index', ['parametros' => Parametro::paginate(15)]);
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


    $material_padrao = Parametro::create($validated);

    if (!$material_padrao) {
      return redirect()->back()
        ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('parametros-index')
      ->with('success', 'Material Padrão cadastrado com sucesso');
  }


  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Parametro $parametro)
  {
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',
      ]
    );

    $parametro->update($validated);

    return redirect()->route('parametros-index')
      ->with('success', 'Material Padrão cadastrado com sucesso');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Parametro $parametro)
  {
    $parametro->delete();

    return redirect()->route('parametros-index')
      ->with('warning', 'Material Padrão removido com sucesso');
  }
}
