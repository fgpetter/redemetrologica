<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;


class FornecedorController extends Controller
{
    /**
   * Gera pagina de listagem de usuários
   *
   * @return View
   **/
  public function index(): View
  {
    $fornecedores = Fornecedor::with('pessoa')->paginate(10);
    $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj')
    ->whereNotIn('id', function ($query) {
      $query->select('pessoa_id')->from('fornecedores');
    })
    ->get();

    return view('painel.fornecedores.index', ['fornecedores' => $fornecedores, 'pessoas' => $pessoas]);
  }

  /**
   * Adiciona fornecedores na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $request->validate(
      [
        'pessoa_uid' => ['required', 'string', 'exists:pessoas,uid'],
      ],
      [
        'pessoa_uid.required' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
        'pessoa_uid.string' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
        'pessoa_uid.exists' => 'Dados inválidos, seleciona uma pessoa e envie novamente'
      ]
    );

    $pessoa = Pessoa::select('id')->where('uid', $request->pessoa_uid)->first();

    // cria um fornecedor vinculado a pessoa
    $fornecedor = Fornecedor::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $pessoa->id,
    ]);

    if (!$fornecedor) {
      return redirect()->back()
        ->with('fornecedor-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }
    
    return redirect()->route('fornecedor-insert', $fornecedor->uid)
      ->with('success', 'Fornecedor cadastrado com sucesso');
  }

  /**
   * Tela de edição de usuário
   *
   * @param Fornecedor $fornecedor
   * @return View
   **/
  public function insert(Fornecedor $fornecedor): View
  {
    return view('painel.fornecedores.insert', ['fornecedor' => $fornecedor]);
  }

  /**
   * Edita dados de usuário
   *
   * @param Request $request
   * @param Fornecedor $fornecedor
   * @return RedirectResponse
   **/
  public function update(Request $request, Fornecedor $fornecedor): RedirectResponse
  {
    $request->merge( return_only_nunbers( $request->only('cpf_cnpj','telefone','telefone_alt','celular') ) );
    
    $validated = $request->validate([
      'nome_razao' => ['required', 'string', 'max:191'],
      'cpf_cnpj' => ['required', 'string', 'max:191', 'unique:pessoas,cpf_cnpj,' . $fornecedor->pessoa->id],
      'rg_ie' => ['nullable', 'string', 'max:191'],
      'telefone' => ['nullable', 'string', 'min:10', 'max:11'],
      'telefone_alt' => ['nullable', 'string', 'min:10', 'max:11'],
      'celular' => ['nullable', 'string', 'min:10', 'max:11'],
      'email' => ['nullable', 'string', 'max:191'],
      'site' => ['nullable', 'string', 'max:191'],
      'observacoes' => ['nullable', 'string', 'max:1000'],

      ],[
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo CPF',
        'cpf_cnpj.unique' => 'CPF ja cadastrado',
        'telefone.min' => 'Telefone inválido',
        'telefone.max' => 'Telefone inválido',
        'telefone_alt.min' => 'Telefone alternativo inválido',
        'telefone_alt.max' => 'Telefone alternativo inválido',
        'celular.min' => 'Celular inválido',
        'celular.max' => 'Celular inválido',
        'email.max' => 'E-mail inválido',
        'site.max' => 'Site inválido',
        'observacoes.max' => 'Observações inválidas',
        ]
    );

    $fornecedor->update($request->only('obsercvacoes'));

    $fornecedor->pessoa->update($validated);

    return redirect()->back()->with('success', 'Fornecedor atualizado com sucesso');
  }

  /**
   * Remove usuário
   *
   * @param Fornecedor $fornecedor
   * @return RedirectResponse
   **/
    public function delete(Fornecedor $fornecedor): RedirectResponse
    {
      $fornecedor->delete();

      return redirect()->route('fornecedor-index')->with('warning', 'Fornecedor removido');
    }


}