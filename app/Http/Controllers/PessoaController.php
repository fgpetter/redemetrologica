<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pessoa;
use Illuminate\Support\Str;
use App\Mail\UserRegistered;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use App\Actions\CreateUserForPessoaAction;


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
    $request->merge( return_only_nunbers( $request->only('cpf_cnpj','telefone','telefone_alt','celular') ) );
    $request->merge([
      'nome_razao' => ($request->get('tipo_pessoa') == 'PJ') ? strtoupper($request->get('nome_razao')) : ucwords(strtolower($request->get('nome_razao'))),
      'nome_fantasia' => strtoupper($request->get('nome_fantasia')),
      'email' => strtolower($request->get('email')),
    ]);

    $validator = Validator::make($request->all(), [
        'nome_razao' => ['required', 'string', 'max:191'],
        'nome_fantasia' => ['nullable', 'string', 'max:191'],
        'cpf_cnpj' => ['required', 'cpf_ou_cnpj', 'max:191', 'unique:pessoas,cpf_cnpj'],
        'rg_ie' => ['nullable', 'string', 'max:191'],
        'insc_municipal' => ['nullable', 'string', 'max:191'],
        'codigo_contabil' => ['nullable', 'string', 'max:191'],
        'tipo_pessoa' => ['required', 'string', 'max:2'],
        'telefone' => ['nullable', 'string', 'min:10', 'max:11'],
        'telefone_alt' => ['nullable', 'string', 'min:10', 'max:11'],
        'celular' => ['nullable', 'string', 'min:10', 'max:11'],
        'email' => ['nullable', 'email', 'max:191'],
        'site' => ['nullable', 'string', 'max:191'],
      ],[
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo documento',
        'cpf_cnpj.unique' => 'Esse CPF/CNPJ ja foi registrado',
        'tipo_pessoa' => 'Preencha o campo tipo de pessoa',
      ]
    );

    if ($validator->fails()) {

      Log::channel('validation')->info("Erro de validação", 
      [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) .'::'. __FUNCTION__ ,
          'errors' => $validator->errors() ?? null,
      ]);

      return back()
      ->withErrors($validator)
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }


    $pessoa = Pessoa::create( $validator->validated() );

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
    $empresas = Pessoa::where('tipo_pessoa', 'PJ')->whereNotIn('id', $pessoa->empresas->pluck('id'))->get();

    return view('painel.pessoas.insert', [
      'pessoa' => $pessoa,
      'empresas' => $empresas
    ]);
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
    $request->merge( return_only_nunbers( $request->only('cpf_cnpj','telefone','telefone_alt','celular') ) );
    $request->merge([
      'nome_razao' => ($request->get('tipo_pessoa') == 'PJ') ? strtoupper($request->get('nome_razao')) : ucwords(strtolower($request->get('nome_razao'))),
      'nome_fantasia' => strtoupper($request->get('nome_fantasia')),
      'email' => strtolower($request->get('email'))
    ]);

    $validator = Validator::make($request->all(), [
        'nome_razao' => ['required', 'string', 'max:191', 'unique:pessoas,cpf_cnpj'],
        'nome_fantasia' => ['nullable', 'string', 'max:191'],
        'cpf_cnpj' => ['required', 'cpf_ou_cnpj', 'max:191', 'unique:pessoas,cpf_cnpj,' . $pessoa->id],
        'rg_ie' => ['nullable', 'string', 'max:191'],
        'insc_municipal' => ['nullable', 'string', 'max:191'],
        'codigo_contabil' => ['nullable', 'string', 'max:191'],
        'tipo_pessoa' => ['required', 'string', 'max:2'],
        'telefone' => ['nullable', 'string', 'min:10', 'max:11'],
        'telefone_alt' => ['nullable', 'string', 'min:10', 'max:11'],
        'celular' => ['nullable', 'string', 'min:10', 'max:11'],
        'email' => ['nullable', 'email', 'max:191'],
        'site' => ['nullable', 'string', 'max:191'],
      ],
      [
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo documento',
        'cpf_cnpj.unique' => 'Esse CPF/CNPJ ja foi registrado',
      ]
    );

    if ($validator->fails()) {

      Log::channel('validation')->info("Erro de validação", 
      [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) .'::'. __FUNCTION__ ,
          'errors' => $validator->errors() ?? null,
      ]);

      return back()
      ->withErrors($validator)
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }

    $pessoa->update( $validator->validated() );

    if( $pessoa->user() ) {
      $pessoa->user()->update([
        'name' => $validator->safe()->nome_razao,
      ]);
    }

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
    $pessoa->user()->delete(); // remove usuário associado
    $pessoa->delete(); // soft delete

    return redirect()->route('pessoa-index')->with('warning', 'Pessoa removida');
  }

  /**
   * Associa uma empresa a uma pessoa
   *
   * @param Request $request
   * @param Pessoa $pessoa
   * @return RedirectResponse
   */
  public function associaEmpresa(Request $request, Pessoa $pessoa): RedirectResponse
  {
    $empresa = Pessoa::where('uid', $request->empresa_id)->first();

    if($request->get('detach')){
      $pessoa->empresas()->detach( $empresa->id );
      
    } else {
      $pessoa->empresas()->attach( $empresa->id );
    }
    
    return back()->with('success', 'Pessoa atualizada com sucesso');
  }

  /**
   * Associa um usuário a uma pessoa
   *
   * @param Pessoa $pessoa
   * @return RedirectResponse
   */
  public function associaUsuario(Pessoa $pessoa): RedirectResponse
  {
    CreateUserForPessoaAction::handle( $pessoa );

    return back()->with('success', 'Pessoa atualizada com sucesso');
  }
}
