<?php

namespace App\Http\Controllers;

use App\Enums\FornecedorArea;
use App\Models\Fornecedor;
use App\Models\Pessoa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FornecedorController extends Controller
{
  /**
   * Gera pagina de listagem de usuários
   *
   * @return View
   **/
  public function index(Request $request)
{
    $name = $request->name;
    $doc = $request->doc;
    $busca_nome = $request->buscanome;
    $busca_doc = preg_replace("/[^0-9]/", "", $request->buscadoc);
    $busca_area = $request->buscaarea;

    $fornecedores = Fornecedor::with('pessoa')
        ->join('pessoas', 'fornecedores.pessoa_id', '=', 'pessoas.id')
        ->select('fornecedores.*')
        ->when($name, function ($query, $name) {
            $query->orderBy('pessoas.nome_razao', $name);
        })
        ->when($doc, function ($query, $doc) {
            $query->orderBy('pessoas.cpf_cnpj', $doc);
        })
        ->when($busca_nome, function ($query, $busca_nome) {
            $query->where('pessoas.nome_razao', 'LIKE', "%{$busca_nome}%");
        })
        ->when($busca_doc, function ($query, $busca_doc) {
            $query->where('pessoas.cpf_cnpj', 'LIKE', "%{$busca_doc}%");
        })
        ->when($busca_area, function ($query, $busca_area) {
            $query->whereJsonContains('fornecedor_area', [$busca_area]);
        })
        ->paginate(10)
        ->withQueryString();

    $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj')
        ->whereNotIn('id', function ($query) {
            $query->select('pessoa_id')->from('fornecedores');
        })
        ->get();

    return view('painel.fornecedores.index', [
        'fornecedores' => $fornecedores,
        'pessoas' => $pessoas
    ]);
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
    $request->merge(return_only_nunbers($request->only('cpf_cnpj', 'telefone', 'telefone_alt', 'celular')));

    $validator = Validator::make($request->all(), [
        'nome_razao' => ['required', 'string', 'max:191'],
        'cpf_cnpj' => ['required', 'string', 'max:191', 'unique:pessoas,cpf_cnpj,' . $fornecedor->pessoa->id],
        'rg_ie' => ['nullable', 'string', 'max:191'],
        'telefone' => ['nullable', 'string', 'min:10', 'max:11'],
        'telefone_alt' => ['nullable', 'string', 'min:10', 'max:11'],
        'celular' => ['nullable', 'string', 'min:10', 'max:11'],
        'site' => ['nullable', 'string', 'max:191'],
        'observacoes' => ['nullable', 'string', 'max:1000'],
        'fornecedor_area' => ['nullable', 'array'],
        'fornecedor_area.*' => [Rule::enum(FornecedorArea::class)],
        'fornecedor_area_atuacao' => ['nullable', 'string', 'max:191'],
        'pessoa_contato' => ['nullable', 'string', 'max:191'],
        'pessoa_contato_email' => ['nullable', 'email', 'max:191'],
        'fornecerdor_desde' => ['nullable', 'date'],
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
        'site.max' => 'Site inválido',
        'observacoes.max' => 'Observações inválidas',
        'fornecedor_area.array' => 'Áreas inválidas',
        'fornecedor_area_atuacao.string' => 'Área de atuação inválida',
        'fornecedor_area_atuacao.max' => 'Área de atuação inválida',
        'pessoa_contato.string' => 'Contato inválido',
        'pessoa_contato.max' => 'Contato inválido',
        'pessoa_contato_email.email' => 'E-mail inválido',
        'pessoa_contato_email.max' => 'E-mail inválido',
        'fornecerdor_desde.date' => 'Data de inicio inválida',
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
      ->withErrors($validator, 'principal')
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }

    $fornecedor->pessoa->update([
      'nome_razao' => $request->nome_razao,
      'cpf_cnpj' => $request->cpf_cnpj,
      'rg_ie' => $request->rg_ie,
      'telefone' => $request->telefone,
      'telefone_alt' => $request->telefone_alt,
      'celular' => $request->celular,
      'site' => $request->site,
    ]);

    $fornecedor->update([
      'fornecedor_area' => $request->fornecedor_area,
      'fornecedor_area_atuacao' => $request->fornecedor_area_atuacao,
      'pessoa_contato' => $request->pessoa_contato,
      'pessoa_contato_email' => $request->pessoa_contato_email,
      'fornecerdor_desde' => $request->fornecerdor_desde,
      'ativo' => $request->ativo,
    ]);

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
