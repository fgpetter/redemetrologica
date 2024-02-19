<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;


class BancosController extends Controller
{
  /**
   * Display a listing of the resource.
   * @return View
   */
  public function index(): View
  {
    return view('painel.bancos.index', ['bancos' => Banco::all()]);
  }

  /**
   * Store a newly created resource in storage.
   *    
   * @param Request $request
   * @return RedirectResponse
   */
  public function store(Request $request): RedirectResponse
  {
    $validated = $request->validate([
        'numero_banco' => ['nullable', 'string'],
        'nome_banco' => ['nullable', 'string'],
        'agencia' => ['nullable', 'string'],
        'conta' => ['nullable', 'string'],
        'movimenta_financeiro' => ['nullable', 'string'],
        'padrao' => ['nullable', 'string'],
      ],[
        'numero_banco.string' => 'Conteúdo inválido',
        'nome_banco.string' => 'Conteúdo inválido',
        'agencia.string' => 'Conteúdo inválido',
        'conta.string' => 'Conteúdo inválido',
        'movimenta_financeiro.string' => 'Conteúdo inválido',
        'padrao.string' => 'Conteúdo inválido',
      ]
    );

    $validated['uid'] = config('hashing.uid');

    $banco = Banco::create($validated);

    if(!$banco){
      return redirect()->back()
        ->with('banco-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('banco-index')
      ->with('banco-success', 'Banco cadastrado com sucesso');
  }


  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Banco $banco)
  {
    $validated = $request->validate([
        'numero_banco' => ['nullable', 'string'],
        'nome_banco' => ['nullable', 'string'],
        'agencia' => ['nullable', 'string'],
        'conta' => ['nullable', 'string'],
        'movimenta_financeiro' => ['nullable', 'string'],
        'padrao' => ['nullable', 'string'],
      ],[
        'numero_banco.string' => 'Conteúdo inválido',
        'nome_banco.string' => 'Conteúdo inválido',
        'agencia.string' => 'Conteúdo inválido',
        'conta.string' => 'Conteúdo inválido',
        'movimenta_financeiro.string' => 'Conteúdo inválido',
        'padrao.string' => 'Conteúdo inválido',
      ]
    );

  $validated['padrao'] = ($request->has('padrao')) ? 1 : 0;
  $validated['movimenta_financeiro'] = ($request->has('padrao')) ? 1 : 0;

  $banco->update($validated);

  return redirect()->route('banco-index')
    ->with('banco-success', 'Banco atualizado com sucesso');

  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Banco $banco)
  {
    $banco->delete();

    return redirect()->route('banco-index')
    ->with('banco-success', 'Banco removido com sucesso');
  }
}
