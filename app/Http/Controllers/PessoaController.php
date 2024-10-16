<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\Endereco;
use App\Models\AgendaCursos;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\LancamentoFinanceiro;
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
    $busca_doc = $request->buscadoc;

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
    $request['cep'] = preg_replace('/[^0-9]/', '', $request->get('cep'));

    $request->validate(
      [
        'nome_razao' => ['required', 'string', 'max:255', 'unique:pessoas,cpf_cnpj'],
        'nome_fantasia' => ['nullable', 'string', 'max:255'],
        'cpf_cnpj' => ['required', 'string', 'max:255'], // TODO - adicionar validação de CPF/CNPJ
        'rg_ie' => ['nullable', 'string', 'max:255'],
        'insc_municipal' => ['nullable', 'string', 'max:255'],
        'codigo_contabil' => ['nullable', 'string', 'max:255'],
        'tipo_pessoa' => ['required', 'string', 'max:2'],
        'telefone' => ['nullable', 'string', 'min:10', 'max:11'],
        'email' => ['nullable', 'string', 'max:255'],
        'cep' => ['required', 'string'],
        'endereco' => ['required', 'string'],
        'complemento' => ['nullable', 'string'],
        'bairro' => ['nullable', 'string'],
        'cidade' => ['required', 'string'],
        'uf' => ['required', 'string'],
      ],
      [
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
      'nome_razao' => ($request->get('tipo_pessoa') == 'PJ') ? strtoupper($request->get('nome_razao')) : ucwords(strtolower($request->get('nome_razao'))),
      'nome_fantasia' => strtoupper($request->get('nome_fantasia')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'insc_municipal' => $request->get('insc_municipal'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email'),
    ]);

    if (!$pessoa) {
      return redirect()->back()->with('error', 'Ocorreu um erro!');
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

    if (!$pessoa) {
      return redirect()->back()->with('error', 'Ocorreu um erro!');
    }

    $pessoa->update([
      'end_padrao' => $endereco->id
    ]);

    return redirect()->route('pessoa-insert', ['pessoa' => $pessoa])->with('success', 'Pessoa cadastrada com sucesso');
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
    $request['cep'] = preg_replace('/[^0-9]/', '', $request->get('cep'));

    $request->validate(
      [
        'nome_razao' => ['required', 'string', 'max:255'],
        'cpf_cnpj' => ['required', 'string', 'max:255', 'unique:pessoas,cpf_cnpj,' . $pessoa->id], // TODO - adicionar validação de CPF/CNPJ
        'tipo_pessoa' => ['required', 'string', 'max:2'],
      ],
      [
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo documento',
      ]
    );

    $pessoa->update([
      'tipo_pessoa' => $request->get('tipo_pessoa'),
      'nome_razao' => ($request->get('tipo_pessoa') == 'PJ') ? strtoupper($request->get('nome_razao')) : ucwords(strtolower($request->get('nome_razao'))),
      'nome_fantasia' => strtoupper($request->get('nome_fantasia')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'insc_municipal' => $request->get('insc_municipal'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email'),
    ]);

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
    // validar se pessoa tem agendas
    $tem_agenda = AgendaCursos::where('instrutor_id', $pessoa->instrutor->id)->first();
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
    if($request->get('detach')){
      $pessoa->empresas()->detach($request->get('empresa_id'));
      
    } else {
      $pessoa->empresas()->sync($request->get('empresa_id'));
    }
    
    return back()->with('success', 'Pessoa atualizada com sucesso');
  }
}
