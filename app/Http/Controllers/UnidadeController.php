<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;


class UnidadeController extends Controller
{

  /**
   * Adiciona usuários na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $request->validate([
      'nome' => ['required', 'string', 'max:255'],
      ],[
        'nome.required' => 'Preencha o campo nome ou razão social',
      ]
    );

    $endereco = Endereco::create([
      'uid' => substr(hrtime(true), -9, 9),
      'pessoa_id' => $request->get('pessoa'),
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf')
    ]);

    $unidade = Unidade::create([
      'uid' => substr(hrtime(true), -9, 9),
      'pessoa_id' => $request->get('pessoa'),
      'endereco_id' => $endereco->id,
      'nome' => strtoupper($request->get('nome')),
      'fone' => $request->get('telefone'),
      'email' => $request->get('email'),
      'cod_laboratorio' => $request->get('cod_laboratorio'),
      'nome_responsavel' => $request->get('nome_responsavel'),
      'responsavel_tecnico' => $request->get('responsavel_tecnico'),
    ]);

    if(!$unidade){
      return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
    }

    $endereco->update(['unidade_id' => $unidade->id]);

    return redirect()->back()->with('unidade-success', 'Unidade cadastrada com sucesso');
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
   * @param Unidade $user
   * @return RedirectResponse
   **/
    public function delete(Unidade $unidade): RedirectResponse
    {
      $unidade->delete();

      return redirect()->back()->with('unidade-success', 'Unidade removida');
    }

}