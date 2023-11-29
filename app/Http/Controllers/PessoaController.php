<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\Datatables;


class PessoaController extends Controller
{

  /**
   *Gera pagina de listagem de usuários
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    if ($request->ajax()) {
      $data = Pessoa::select('*');

      return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $editUrl = route('pessoa-insert', ['pessoa' => $row->uid]);
          $actionBtn = '<a href="' . $editUrl . '" class="edit btn btn-success btn-sm">Editar</a> ';
          $actionBtn .= '<form method="POST" action="' . route('pessoa-delete', $row->uid) . '" style="display: inline;">';
          $actionBtn .= csrf_field();
          $actionBtn .= '<button type="submit" class="delete btn btn-danger btn-sm">Delete</button>';
          $actionBtn .= '</form>';
          return $actionBtn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    return view('pessoas.index');
  }


  /**
   * Adiciona usuários na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {

    $request->validate(
      [
        'nome_razao' => ['required', 'string', 'max:255'],
        'cpf_cnpj' => ['required', 'string', 'max:255'], // TODO - adicionar validação de CPF/CNPJ
        'tipo_pessoa' => ['required', 'string', 'max:2'],
      ],
      [
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo documento',
      ]
    );

    $pessoa = Pessoa::create([
      'uid' => substr(hrtime(true), -9, 9),
      'tipo_pessoa' => $request->get('tipo_pessoa'),
      'nome_razao' => ($request->get('tipo_pessoa') == 'PJ') ? strtoupper($request->get('nome_razao')) : ucfirst($request->get('nome_razao')),
      'nome_fantasia' => strtoupper($request->get('nome_fantasia')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'insc_municipal' => $request->get('insc_municipal'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email'),
      'codigo_contabil' => $request->get('codigo_contabil'),
    ]);

    if (!$pessoa) {
      return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
    }

    $endereco = Endereco::create([
      'uid' => substr(hrtime(true), -9, 9),
      'pessoa_id' => $pessoa->id,
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf'),

    ]);

    return redirect()->route('pessoa-insert', ['pessoa' => $pessoa])->with('succes', 'Pessoa cadastrada com sucesso');
  }

  /**
   * Tela de edição de usuário
   *
   * @param Pessoa $pessoa
   * @return View
   **/
  public function insert(Pessoa $pessoa): View
  {
    return view('pessoas.insert', ['pessoa' => $pessoa]);
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
  public function delete(Pessoa $pessoa): RedirectResponse
  {
    $pessoa->delete();

    return redirect()->route('pessoa-index')->with('update-success', 'Pessoa removida');
  }
}
