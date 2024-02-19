<?php

namespace App\Http\Controllers;

use App\Models\CentroCusto;
use Illuminate\Http\Request;

class CentroCustoController extends Controller
{
    /**
   * Display a listing of the resource.
   */
  public function index()
  {

    return view('painel.centro-custo.index',['centrocustos' => CentroCusto::all() ]);
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'descricao' => ['required', 'string'],
      ],[
      'descricao.required' => 'Preencha o campo Descrição',
    
      ]
      );
    
      $validated['uid'] = config('hashing.uid');
    
      $centro_custo = CentroCusto::create($validated);
    
      if(!$centro_custo){
      return redirect()->back()
      ->with('centro-custo-error', 'Ocorreu um erro! Revise os dados e tente novamente');
      }
    
      return redirect()->route('centro-custo-index')
      ->with('centro-custo-success', 'Centro de Custo cadastrado com sucesso');
    
  }


  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, CentroCusto $centroCusto)
  {
    $validated = $request->validate([
      'descricao' => ['required', 'string'],
      ],[
      'descricao.required' => 'Preencha o campo Descrição',  
      ]
    );  
  
    $centroCusto->update($validated);  
  
    return redirect()->route('centro-custo-index')
      ->with('centro-custo-success', 'Centro de Custo cadastrado com sucesso');

  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(CentroCusto $centroCusto)
  {
    $centroCusto->delete();

    return redirect()->route('centro-custo-index')
    ->with('centro-custo-success', 'Centro de Custo removido com sucesso');
  }
}
