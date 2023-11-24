<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;


class PessoaController extends Controller
{
    /**
   * Gera pagina de listagem de usuários
   *
   * @return View
   **/
  public function index(): View
  {
    $pessoas = Pessoa::all();
    return view('painel.pessoas.index', ['pessoas' => $pessoas]);
  }

  /**
   * Adiciona pessoa
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $request->validate([
      'nome_razao' => ['required', 'string', 'max:255'],
      'cpf_cnpj' => ['required', 'string', 'max:255'], // TODO - adicionar validação de CPF/CNPJ
      'tipo_pessoa' => ['required', 'string', 'max:2'],
      'cep' => ['required', 'string'],
      'endereco' => ['required', 'string'],
      'cidade' => ['required', 'string'],
      'uf' => ['required', 'string'],
      ],[
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo documento',
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o uf',
      ]
    );

    $pessoa = Pessoa::create([
      'uid' => config('hashing.uid'),
      'tipo_pessoa' => $request->get('tipo_pessoa'),
      'nome_razao' => ($request->get('tipo_pessoa') == 'PJ') ? strtoupper($request->get('nome_razao')) : ucfirst($request->get('nome_razao')) ,
      'nome_fantasia' => strtoupper($request->get('nome_fantasia')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'insc_municipal' => $request->get('insc_municipal'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email'),
      'codigo_contabil' => $request->get('codigo_contabil'),
    ]);

    if(!$pessoa){
      return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
    }

    $endereco = Endereco::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $pessoa->id,
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf'),

    ]);

    if(!$pessoa){
      return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
    }

    $pessoa->update([
      'end_padrao' => $endereco->id
    ]);

    return redirect()->route('pessoa-insert', ['pessoa' => $pessoa])->with('succes', 'Pessoa cadastrada com sucesso');
  }

  /**
   * Tela de edição de pessoa
   *
   * @param Pessoa $pessoa
   * @return View
   **/
  public function insert(Pessoa $pessoa): View
  {
    return view('painel.pessoas.insert', ['pessoa' => $pessoa]);
  }

  /**
   * Edita dados de pessoa
   *
   * @param Request $request
   * @param Pessoa $pessoa
   * @return RedirectResponse
   **/
  public function update(Request $request, Pessoa $pessoa): RedirectResponse
  {
    $request->validate([
      'nome_razao' => ['required', 'string', 'max:255'],
      'cpf_cnpj' => ['required', 'string', 'max:255'], // TODO - adicionar validação de CPF/CNPJ
      'tipo_pessoa' => ['required', 'string', 'max:2'],
      ],[
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo documento',
      ]
    );

    $pessoa->update([
      'tipo_pessoa' => $request->get('tipo_pessoa'),
      'nome_razao' => ($request->get('tipo_pessoa') == 'PJ') ? strtoupper($request->get('nome_razao')) : ucfirst($request->get('nome_razao')) ,
      'nome_fantasia' => strtoupper($request->get('nome_fantasia')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'insc_municipal' => $request->get('insc_municipal'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email'),
      'codigo_contabil' => $request->get('codigo_contabil'),
    ]);

    return redirect()->route('pessoa-insert', ['pessoa' => $pessoa])->with('succes', 'Pessoa cadastrada com sucesso');

  }

  /**
   * Remove usuário
   *
   * @param User $user
   * @return RedirectResponse
   **/
    public function delete(Pessoa $pessoa): RedirectResponse
    {
      $pessoa->delete();
      return redirect()->route('pessoa-index')->with('update-success', 'Pessoa removida');
    }

}