<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Funcionario;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;


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
      'admissao' => ['required', 'date'],
      'curriculo' => ['file','mimes:doc,pdf,docx','max:5242880'] //5mb
      ],[
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo CPF',
        'cpf_cnpj.min' => 'CPF inválido',
        'cpf_cnpj.max' => 'CPF inválido',
        'cep.required' => 'Preencha o campo CEP',
        'cep.min' => 'CEP inválido',
        'cep.mmax' => 'CEP inválido',
        'endereco.required' => 'Preencha o campo endereco',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Inválido',
        'uf.min' => 'Inválido',
        'uf.max' => 'Inválido',
        'admissao.required' => 'Preencha o campo admissão',
        'admissao.date' => 'A data não é válida',
        'curriculo.mimes' => 'Somente arquivos DOC, DOCX e PDF',
        'curriculo.max' => 'Tamanho máximo 5MB'
      ]
    );

    // cria uma pessoa
    $pessoa = Pessoa::create([
      'uid' => config('hashing.uid'),
      'tipo_pessoa' => 'PF',
      'nome_razao' => ucfirst($request->get('nome_razao')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email'),
    ]);

    if(!$pessoa){
      return redirect()->back()
        ->with('funcionario-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    // cria um endereço vinculado a pessoa
    $endereco = Endereco::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $pessoa->id,
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf')
    ]);

    // Adiciona endereço padrao
    if($request->end_padrao){
      $pessoa->update(['end_padrao' => $endereco->id]);
    }

    if(!$endereco){
      return redirect()->back()
        ->with('funcionario-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    // se foi enviado currículo
    if ($request->hasFile('curriculo')) {
      $fileName = sanitizeFileName( pathinfo($request->file('curriculo')->getClientOriginalName(), PATHINFO_FILENAME) );
      $extension = $request->file('curriculo')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('curriculo')->move(public_path('curriculos'), $fileName);
      $curriculo = 'curriculos/' . $fileName;
    }

    // cria um funcionário vinculado a pessoa
    $funcionario = Funcionario::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $pessoa->id,
      'cargo' => $request->get('cargo'),
      'setor' => $request->get('setor'),
      'admissao' => $request->get('admissao'),
      'demissao' => $request->get('demissao'),
      'observacoes' => $request->get('observacoes'),
      'curriculo' => $curriculo ?? null
    ]);

    if(!$funcionario){
      return redirect()->back()
      ->with('funcionario-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('funcionario-index')
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
   * @param Funcionario $user
   * @return RedirectResponse
   **/
  public function update(Request $request, Funcionario $funcionario): RedirectResponse
  {
    $request->validate([
      'nome_razao' => ['required', 'string', 'max:255'],
      'cpf_cnpj' => ['required', 'string', 'max:14','min:14'], // TODO - adicionar validação de CPF/CNPJ
      'cep' => ['required', 'string', 'max:9', 'min:9'],
      'endereco' => ['required', 'string', 'max:255'],
      'cidade' => ['required', 'string', 'max:255'],
      'uf' => ['required', 'string', 'max:2', 'min:2'],
      'admissao' => ['required', 'date'],
      'curriculo' => ['file','mimes:doc,pdf,docx','max:5242880'] //5mb
      ],[
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo CPF',
        'cpf_cnpj.min' => 'CPF inválido',
        'cpf_cnpj.max' => 'CPF inválido',
        'cep.required' => 'Preencha o campo CEP',
        'cep.min' => 'CEP inválido',
        'cep.mmax' => 'CEP inválido',
        'endereco.required' => 'Preencha o campo endereco',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Inválido',
        'uf.min' => 'Inválido',
        'uf.max' => 'Inválido',
        'admissao.required' => 'Preencha o campo admissão',
        'admissao.date' => 'A data não é válida',
        'curriculo.mimes' => 'Somente arquivos DOC, DOCX e PDF',
        'curriculo.max' => 'Tamanho máximo 5MB'
      ]
    );

    // se foi enviado currículo
    if ($request->hasFile('curriculo')) {
      $fileName = sanitizeFileName( pathinfo($request->file('curriculo')->getClientOriginalName(), PATHINFO_FILENAME) );
      $extension = $request->file('curriculo')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('curriculo')->move(public_path('curriculos'), $fileName);
      $curriculo = 'curriculos/' . $fileName;
    }
    

    $funcionario->update([
      'cargo' => $request->get('cargo'),
      'setor' => $request->get('setor'),
      'admissao' => $request->get('admissao'),
      'demissao' => $request->get('demissao'),
      'observacoes' => $request->get('observacoes'),
      'curriculo' => $curriculo ?? null
    ]);

    $funcionario->pessoa->update([
      'nome_razao' => ucfirst($request->get('nome_razao')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email')
    ]);

    $funcionario->pessoa->enderecos->first()->update([
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf')
    ]);

    // Adiciona endereço padrao
    if($request->end_padrao){
      $funcionario->pessoa->update(['end_padrao' => $funcionario->pessoa->enderecos->first()->id]);
    }
    

    return redirect()->back()->with('funcionario-success', 'Funcionario atualizado com sucesso');
  }

  /**
   * Remove usuário
   *
   * @param User $user
   * @return RedirectResponse
   **/
    public function delete(Funcionario $funcionario): RedirectResponse
    {
      if (File::exists(public_path($funcionario->curriculo))) {
        File::delete(public_path($funcionario->curriculo));
      }

      Pessoa::where('id', $funcionario->pessoa_id)->delete();

      $funcionario->delete();

      return redirect()->route('funcionario-index')->with('warning', 'Funcionario removido');
    }

  /**
   * Remove arquivo de curriculo
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function curriculoDelete(Funcionario $funcionario): RedirectResponse
  {
    // deleta arquivo anterior
    if (File::exists(public_path($funcionario->curriculo))) {
      File::delete(public_path($funcionario->curriculo));
    }
  
    $funcionario->update(['curriculo' => null]);

    return redirect()->back()->with('funcionario-success', 'Curriculo removido');
  }

}