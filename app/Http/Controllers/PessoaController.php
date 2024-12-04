<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pessoa;
use Illuminate\Support\Str;
use App\Models\AgendaCursos;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\LancamentoFinanceiro;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;

class PessoaController extends Controller
{

  /**
   *Gera pagina de listagem de usuários
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $name = $request->name;
    $data = $request->data;
    $doc = $request->doc;
    $busca_nome = $request->buscanome;
    $busca_doc = preg_replace("/[^0-9]/", "", $request->buscadoc);

    $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj', 'created_at')
      ->when($name, function (Builder $query, $name) {
        $query->orderBy('nome_razao', $name);
      })
      ->when($data, function (Builder $query, $data) {
        $query->orderBy('created_at', $data);
      })
      ->when($doc, function (Builder $query, $doc) {
        $query->orderBy('cpf_cnpj', $doc);
      })
      ->when($busca_nome, function (Builder $query, $busca_nome) {
        $query->where('nome_razao', 'LIKE', "%$busca_nome%");
      })
      ->when($busca_doc, function (Builder $query, $busca_doc) {
        $query->where('cpf_cnpj', 'LIKE', "%$busca_doc%");
      })
      ->paginate(10);

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
    $request['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $request->get('cpf_cnpj'));
    $request['telefone'] = preg_replace('/[^0-9]/', '', $request->get('telefone'));
    $request['telefone_alt'] = preg_replace('/[^0-9]/', '', $request->get('telefone'));
    $request['celular'] = preg_replace('/[^0-9]/', '', $request->get('telefone'));
    $request['nome_razao'] = ($request->get('tipo_pessoa') == 'PJ') ? strtoupper($request->get('nome_razao')) : ucwords(strtolower($request->get('nome_razao')));
    $request['nome_fantasia'] = strtoupper($request->get('nome_fantasia'));

    $validated = $request->validate(
      [
        'nome_razao' => ['required', 'string', 'max:255', 'unique:pessoas,cpf_cnpj'],
        'nome_fantasia' => ['nullable', 'string', 'max:255'],
        'cpf_cnpj' => ['required', 'string', 'max:255', 'unique:pessoas,cpf_cnpj'], // TODO - adicionar validação de CPF/CNPJ
        'rg_ie' => ['nullable', 'string', 'max:255'],
        'insc_municipal' => ['nullable', 'string', 'max:255'],
        'codigo_contabil' => ['nullable', 'string', 'max:255'],
        'tipo_pessoa' => ['required', 'string', 'max:2'],
        'telefone' => ['nullable', 'string', 'min:10', 'max:11'],
        'telefone_alt' => ['nullable', 'string', 'min:10', 'max:11'],
        'celular' => ['nullable', 'string', 'min:10', 'max:11'],
        'email' => ['nullable', 'string', 'max:255'],
        'site' => ['nullable', 'string', 'max:255'],
      ],
      [
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo documento',
        'cpf_cnpj.unique' => 'Esse CPF/CNPJ ja foi registrado',
      ]
    );

    $pessoa = Pessoa::create( $validated);

    if (!$pessoa) {
      return redirect()->back()->with('error', 'Ocorreu um erro!');
    }

    $uid = Pessoa::find($pessoa->id)->uid;

    return redirect()->route('pessoa-insert', ['pessoa' => $uid])->with('success', 'Pessoa cadastrada com sucesso');
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
    $request['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $request->get('cpf_cnpj'));
    $request['telefone'] = preg_replace('/[^0-9]/', '', $request->get('telefone'));
    $request['telefone_alt'] = preg_replace('/[^0-9]/', '', $request->get('telefone'));
    $request['celular'] = preg_replace('/[^0-9]/', '', $request->get('telefone'));
    $request['nome_razao'] = ($request->get('tipo_pessoa') == 'PJ') ? strtoupper($request->get('nome_razao')) : ucwords(strtolower($request->get('nome_razao')));
    $request['nome_fantasia'] = strtoupper($request->get('nome_fantasia'));

    $validated = $request->validate(
      [
        'nome_razao' => ['required', 'string', 'max:255', 'unique:pessoas,cpf_cnpj'],
        'nome_fantasia' => ['nullable', 'string', 'max:255'],
        'cpf_cnpj' => ['required', 'string', 'max:255', 'unique:pessoas,cpf_cnpj,' . $pessoa->id], // TODO - adicionar validação de CPF/CNPJ
        'rg_ie' => ['nullable', 'string', 'max:255'],
        'insc_municipal' => ['nullable', 'string', 'max:255'],
        'codigo_contabil' => ['nullable', 'string', 'max:255'],
        'tipo_pessoa' => ['required', 'string', 'max:2'],
        'telefone' => ['nullable', 'string', 'min:10', 'max:11'],
        'telefone_alt' => ['nullable', 'string', 'min:10', 'max:11'],
        'celular' => ['nullable', 'string', 'min:10', 'max:11'],
        'email' => ['nullable', 'string', 'max:255'],
        'site' => ['nullable', 'string', 'max:255'],
      ],
      [
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo documento',
        'cpf_cnpj.unique' => 'Esse CPF/CNPJ ja foi registrado',
      ]
    );

    $pessoa->update( $validated );

    return back()->with('success', 'Pessoa atualizada com sucesso');
  }

  /**
   * Remove usuário
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function delete(Pessoa $pessoa): RedirectResponse
  {
    // validar se pessoa tem lancamentos financeiros
    $tem_lac_financ = LancamentoFinanceiro::where('pessoa_id', $pessoa->id)->first();

    // validar se pessoa é instrutor em curso
    $tem_agenda = AgendaCursos::where('instrutor_id', $pessoa->instrutor?->id)->first();
    if($tem_agenda) { $pessoa->instrutor->delete(); }
    
    if ($tem_lac_financ || $tem_agenda) {
      $pessoa->delete();
    } else {
      $pessoa->forceDelete();
    }

    return redirect()->route('pessoa-index')->with('warning', 'Pessoa removida');
  }

  public function associaEmpresa(Request $request, Pessoa $pessoa): RedirectResponse
  {
    $pessoa->empresas()->sync($request->get('empresa_id'));
    // if($request->get('detach')){
    //   $pessoa->empresas()->detach($request->get('empresa_id'));
      
    // } else {
    //   $pessoa->empresas()->sync($request->get('empresa_id'));
    // }
    
    return back()->with('success', 'Pessoa atualizada com sucesso');
  }
  public function associaUsuario(Request $request, Pessoa $pessoa): RedirectResponse
  {
    $request->validate([
      'nome' => ['required', 'string'],
      'email' => ['required', 'string', 'email', 'unique:users,email'],
    ]);

    $random_password = Str::random(8);

    $user = User::create([
      'name' => $request->get('nome'),
      'email' => $request->get('email'),
      'password' => Hash::make('Password'),
      'temporary_password' => 1
    ]);

    $pessoa->update([ 'user_id' => $user->id ]);

    return back()->with('success', 'Pessoa atualizada com sucesso');
  }
}
