<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Avaliador;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;


class AvaliadorController extends Controller
{
    /**
   * Gera pagina de listagem de usuários
   *
   * @return View
   **/
  public function index(): View
  {
    $avaliadores = Avaliador::all();
    return view('painel.avaliadores.index', ['avaliadores' => $avaliadores]);
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
        ->with('avaliador-error', 'Ocorreu um erro! Revise os dados e tente novamente');
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
    $avaliador = Avaliador::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $pessoa->id,
      'exp_min_comprovada' => $request->get('exp_min_comprovada') ?? 0,
      'curso_incerteza' => $request->get('curso_incerteza') ?? 0,
      'curso_iso' => $request->get('curso_iso') ?? 0,
      'curso_aud_interna' => $request->get('curso_aud_interna') ?? 0,
      'parecer_psicologico' => $request->get('parecer_psicologico') ?? 0,
      'data_ingresso' => $request->get('data_ingresso'),
      'curriculo' => $curriculo ?? null
    ]);

    if(!$avaliador){
      return redirect()->back()
      ->with('avaliador-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('avaliador-index')
      ->with('avaliador-success', 'Avaliador cadastrada com sucesso');
  }

  /**
   * Tela de edição de usuário
   *
   * @param Avaliador $avaliador
   * @return View
   **/
  public function insert(Avaliador $avaliador): View
  {
    return view('painel.avaliadores.insert', ['avaliador' => $avaliador]);
  }

  /**
   * Edita dados de usuário
   *
   * @param Request $request
   * @param Avaliador $user
   * @return RedirectResponse
   **/
  public function update(Request $request, Avaliador $avaliador): RedirectResponse
  {
    $request->validate([
      'nome_razao' => ['required', 'string', 'max:255'],
      'cpf_cnpj' => ['required', 'string', 'max:14','min:14'], // TODO - adicionar validação de CPF/CNPJ
      'cep' => ['required', 'string', 'max:9', 'min:9'],
      'endereco' => ['required', 'string', 'max:255'],
      'cidade' => ['required', 'string', 'max:255'],
      'uf' => ['required', 'string', 'max:2', 'min:2'],
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

    $avaliador->update([
      'exp_min_comprovada' => $request->get('exp_min_comprovada') ?? 0,
      'curso_incerteza' => $request->get('curso_incerteza') ?? 0,
      'curso_iso' => $request->get('curso_iso') ?? 0,
      'curso_aud_interna' => $request->get('curso_aud_interna') ?? 0,
      'parecer_psicologico' => $request->get('parecer_psicologico') ?? 0,
      'data_ingresso' => $request->get('data_ingresso'),
      'curriculo' => $curriculo ?? null
    ]);

    $avaliador->pessoa->update([
      'nome_razao' => ucfirst($request->get('nome_razao')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email')
    ]);

    $avaliador->pessoa->enderecos->first()->update([
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf')
    ]);

    return redirect()->back()->with('avaliador-success', 'Avaliador atualizado com sucesso');
  }

  /**
   * Remove usuário
   *
   * @param User $user
   * @return RedirectResponse
   **/
    public function delete(Avaliador $avaliador): RedirectResponse
    {
      if (File::exists(public_path($avaliador->curriculo))) {
        File::delete(public_path($avaliador->curriculo));
      }

      Pessoa::where('id', $avaliador->pessoa_id)->delete();
      $avaliador->delete();

      return redirect()->route('avaliador-index')->with('avaliador-success', 'Avaliador removido');
    }

  /**
   * Remove arquivo de curriculo
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function curriculoDelete(Avaliador $avaliador): RedirectResponse
  {
    if (File::exists(public_path($avaliador->curriculo))) {
      File::delete(public_path($avaliador->curriculo));
    }
  
    $avaliador->update(['curriculo' => null]);

    return redirect()->back()->with('avaliador-success', 'Curriculo removido');
  }

}