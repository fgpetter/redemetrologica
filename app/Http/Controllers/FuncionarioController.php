<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Funcionario;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;


class FuncionarioController extends Controller
{
    /**
   * Gera pagina de listagem de usuários
   *
   * @return View
   **/
  public function index(): View
  {
    $funcionarios = Funcionario::all();
    return view('painel.funcionarios.index', ['funcionarios' => $funcionarios]);
  }

  /**
   * Adiciona usuários na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $request->validate([
      'nome_razao' => ['required', 'string', 'max:255'],
      'cpf_cnpj' => ['required', 'string', 'max:14','min:14'], // TODO - adicionar validação de CPF/CNPJ
      'cep' => ['required', 'string', 'max:9', 'min:9'],
      'endereco' => ['required', 'string', 'max:255'],
      'cidade' => ['required', 'string', 'max:255'],
      'uf' => ['required', 'string', 'max:2', 'min:2'],
      'admissao' => ['required', 'date']
      ],[
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo CPF',
        'cpf_cnpj.min' => 'O CPF não é válido',
        'cpf_cnpj.max' => 'O CPF não é válido',
        'cep.required' => 'Preencha o campo CEP',
        'cep.min' => 'O cep não é válido',
        'cep.mmax' => 'O cep não é válido',
        'endereco.required' => 'Preencha o campo endereco',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o campo estado',
        'uf.min' => 'Preencha a sigla do estado',
        'uf.max' => 'Preencha a sigla do estado',
        'admissao.required' => 'Preencha o campo estado',
        'admissao.date' => 'A data não é válida',
      ]
    );

    $pessoa = Pessoa::create([
      'uid' => substr(hrtime(true), -9, 9),
      'tipo_pessoa' => 'PF',
      'nome_razao' => ucfirst($request->get('nome_razao')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email'),
    ]);

    if(!$pessoa){
      return redirect()->back()
        ->withInput($request->input())
        ->with('funcionario-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    $endereco = Endereco::create([
      'uid' => substr(hrtime(true), -9, 9),
      'pessoa_id' => $pessoa->id,
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf')
    ]);

    if(!$endereco){
      return redirect()->back()
        ->withInput($request->input())
        ->with('funcionario-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    $funcionario = Funcionario::create([
      'uid' => substr(hrtime(true), -9, 9),
      'pessoa_id' => $pessoa->id,
      'cargo' => $request->get('cargo'),
      'setor' => $request->get('setor'),
      'admissao' => $request->get('admissao'),
      'demissao' => $request->get('demissao'),
      'observacoes' => $request->get('observacoes'),
      'curriculo' => $request->get('curriculo')
    ]);

    if(!$funcionario){
      return redirect()->back()
      ->withInput($request->input())
      ->with('funcionario-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('funcionario-index', ['funcionario' => $funcionario])
      ->with('funcionario-success', 'Funcionario cadastrada com sucesso');
  }

  /**
   * Tela de edição de usuário
   *
   * @param Funcionario $funcionario
   * @return View
   **/
  public function insert(Funcionario $funcionario): View
  {
    return view('painel.funcionarios.insert', ['funcionario' => $funcionario]);
  }

  /**
   * Edita dados de usuário
   *
   * @param Request $request
   * @param User $user
   * @return RedirectResponse
   **/
  // public function update(Request $request, User $user): RedirectResponse
  // {
  //   $request->validate([
  //     'nome' => ['required', 'string', 'max:255'],
  //     'email' => ['unique:users,email,'.$user->id,'required', 'string', 'email'],
  //     ],[
  //     'nome.required' => 'Preencha o campo nome',
  //     'email.required' => 'Preencha o campo email',
  //     'email.email' => 'Não é um email válido',
  //     'email.unique' => 'Esse email já está em uso',
  //     ]
  //   );

  //   $user->update([
  //     'name' => $request->get('nome'),
  //     'email' => $request->get('email'),
  //     'password' => Hash::make('Password')
  //   ]);

  //   if(!$user){
  //     return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
  //   }

  //   return redirect()->route('user-index')->with('update-success', 'Usuário atualizado');
  // }

  /**
   * Remove usuário
   *
   * @param User $user
   * @return RedirectResponse
   **/
    public function delete(Funcionario $funcionario): RedirectResponse
    {
      $funcionario->delete();

      return redirect()->route('funcionario-index')->with('funcionario-success', 'Funcionario removido');
    }

}