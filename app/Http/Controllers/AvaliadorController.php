<?php

namespace App\Http\Controllers;

use App\Models\AvaliacaoAvaliador;
use App\Models\Avaliador;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;


class AvaliadorController extends Controller
{
  /**
   * Gera pagina de listagem de usuários
   *
   * @return View
   **/
  public function index(): View
  {
    $avaliadores = Avaliador::paginate(10);
    $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj')
      ->whereNotIn('id', function ($query) {
        $query->select('pessoa_id')->from('avaliadores');
      })
      ->get();

    return view('painel.avaliadores.index', ['avaliadores' => $avaliadores, 'pessoas' => $pessoas]);
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
        'pessoa_uid' => ['required', 'string', 'exists:pessoas,uid'],
      ],
      [
        'pessoa_uid.required' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
        'pessoa_uid.string' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
        'pessoa_uid.exists' => 'Dados inválidos, seleciona uma pessoa e envie novamente'
      ]
    );

    $pessoa = Pessoa::select('id')->where('uid', $request->pessoa_uid)->first();

    // cria um avaliador vinculado a pessoa
    $avaliador = Avaliador::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $pessoa->id,
    ]);

    if (!$avaliador) {
      return redirect()->back()
        ->with('avaliador-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('avaliador-insert', $avaliador->uid)
      ->with('success', 'Avaliador cadastrado com sucesso');
  }

  /**
   * Adiciona usuários na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function createAvaliacao(Request $request, Avaliador $avaliador): RedirectResponse
  {
    $request->validate(
      [
        'empresa' => ['nullable', 'string'],
        'situacao' => ['required', Rule::in(['AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LÍDER', 'ESPECIALISTA'])],
        'data' => ['nullable', 'date'],
      ],
      [
        'empresa.string' => 'Dado inváido.',
        'situacao.required' => 'Selecione uma opção válida',
        'situacao.in' => 'Selecione uma opção válida',
        'data.date' => 'Dado inváido.',
      ]
    );

    $avaliacao = AvaliacaoAvaliador::create([
      'uid' => config('hashing.uid'),
      'avaliador_id' => $avaliador->id,
      'empresa' => $request->empresa,
      'data' => $request->sata,
      'situacao' => $request->situacao,
    ]);

    if (!$avaliacao) {
      return redirect()->back()
        ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('avaliador-insert', $avaliador->uid)
      ->with('success', 'Avaliação cadastrada com sucesso');
  }

  /**
   * Tela de edição de usuário
   *
   * @param Avaliador $avaliador
   * @return View
   **/
  public function insert(Avaliador $avaliador): View
  {
    $avaliacoes = AvaliacaoAvaliador::where('avaliador_id', $avaliador->id)->get();
    return view('painel.avaliadores.insert', ['avaliador' => $avaliador, 'avaliacoes' => $avaliacoes]);
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
    $request->validate(
      [
        'nome_razao' => ['required', 'string', 'max:255'],
        'cpf_cnpj' => ['required', 'string', 'max:14', 'min:14'], // TODO - adicionar validação de CPF/CNPJ
        'curriculo' => ['file', 'mimes:doc,pdf,docx', 'max:5242880'] //5mb
      ],
      [
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo CPF',
        'cpf_cnpj.min' => 'CPF inválido',
        'cpf_cnpj.max' => 'CPF inválido',
        'curriculo.mimes' => 'Somente arquivos DOC, DOCX e PDF',
        'curriculo.max' => 'Tamanho máximo 5MB'
      ]
    );

    // se foi enviado currículo
    if ($request->hasFile('curriculo')) {
      $fileName = sanitizeFileName(pathinfo($request->file('curriculo')->getClientOriginalName(), PATHINFO_FILENAME));
      $extension = $request->file('curriculo')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('curriculo')->move(public_path('curriculos'), $fileName);
      $curriculo = 'curriculos/' . $fileName;
      $avaliador->update([
        'curriculo' => $curriculo
      ]);
    }

    $avaliador->update([
      'exp_min_comprovada' => $request->get('exp_min_comprovada') ?? 0,
      'curso_incerteza' => $request->get('curso_incerteza') ?? 0,
      'curso_iso' => $request->get('curso_iso') ?? 0,
      'curso_aud_interna' => $request->get('curso_aud_interna') ?? 0,
      'parecer_psicologico' => $request->get('parecer_psicologico') ?? 0,
      'data_ingresso' => $request->get('data_ingresso'),
    ]);

    $avaliador->pessoa->update([
      'nome_razao' => ucfirst($request->get('nome_razao')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email')
    ]);

    return redirect()->back()->with('success', 'Avaliador atualizado com sucesso');
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

    $avaliador->delete();

    return redirect()->route('avaliador-index')->with('warning', 'Avaliador removido');
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

    return redirect()->back()->with('success', 'Curriculo removido');
  }
}
