<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\Avaliador;
use App\Models\AreaAtuacao;
use App\Models\Qualificacao;
use Illuminate\Http\Request;
use App\Models\AvaliadorArea;
use App\Models\StatusAvaliador;
use Illuminate\Validation\Rule;
use App\Models\AvaliacaoAvaliador;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Models\CertificadoAvaliador;
use Illuminate\Support\Facades\File;
use Illuminate\Http\RedirectResponse;


class AvaliadorController extends Controller
{
  /**
   * Gera pagina de listagem de avaliadores
   *
   * @return View
   **/
  public function index(): View
  {
    $avaliadores = Avaliador::with('pessoa')->paginate(10);
    $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj')
      ->whereNotIn('id', function ($query) {
        $query->select('pessoa_id')->from('avaliadores');
      })
      ->get();

    return view('painel.avaliadores.index', ['avaliadores' => $avaliadores, 'pessoas' => $pessoas]);
  }

  /**
   * Adiciona avaliadores na base
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
   * Adiciona avaliacao
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
   * Tela de edição de avaliador
   *
   * @param Avaliador $avaliador
   * @return View
   **/
  public function insert(Avaliador $avaliador): View
  {
    // carrega avaliações que esse avaliador realizou
    $avaliacoes = AvaliacaoAvaliador::where('avaliador_id', $avaliador->id)->get();

    // carrega qualificações do avaliador
    $qualificacoes = Qualificacao::where('avaliador_id', $avaliador->id)->get();
    $qualificacoes_list = [
      'atividades' => DB::table('qualificacoes')->distinct()->get(['atividade']),
      'instrutores' => DB::table('qualificacoes')->distinct()->get(['instrutor']),
    ];

    // carrega areas de atuação do avaliador
    $areas_atuacao = AreaAtuacao::select('id', 'uid', 'descricao')->get();

    return view(
      'painel.avaliadores.insert',
      [
        'avaliador' => $avaliador,
        'avaliacoes' => $avaliacoes,
        'qualificacoes' => $qualificacoes,
        'qualificacoes_list' => $qualificacoes_list,
        'areas_atuacao' => $areas_atuacao,
      ]
    );
  }

  /**
   * Edita dados de avaliador
   *
   * @param Request $request
   * @param Avaliador $user
   * @return RedirectResponse
   **/
  public function update(Request $request, Avaliador $avaliador): RedirectResponse
  {
    $request->merge( return_only_nunbers( $request->only('cpf_cnpj') ) );
    $request->validate(
      [
        'nome_razao' => ['required', 'string', 'max:191'],
        'cpf_cnpj' => ['required', 'string', 'max:191', 'unique:pessoas,cpf_cnpj,' . $avaliador->pessoa->id], // TODO - adicionar validação de CPF/CNPJ
        'curriculo' => ['file', 'mimes:doc,pdf,docx', 'max:5242880'], //5mb
        'data_ingresso' => ['nullable', 'date'],
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
   * Remove avaliador
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

  /**
   * Adiciona qualificação
   *
   * @param Avaliador $avaliador
   * @param Request $request
   * @return RedirectResponse
   **/
  public function createQualificacao(Avaliador $avaliador, Request $request): RedirectResponse
  {
    $validatedData = $request->validate([
      'ano' => ['nullable', 'string'],
      'atividade' => ['nullable', 'string'],
      'instrutor' => ['nullable', 'string'],
    ], [
      'ano.string' => 'O campo Ano deve ser uma string.',
      'atividade.string' => 'O campo Atividade deve ser uma string.',
      'instrutor.string' => 'O campo Instrutor deve ser uma string.',
    ]);

    if (!$avaliador) {
      return redirect()->back()->with('error', 'Houve um erro, tente novamente');
    }

    $validatedData['avaliador_id'] = $avaliador->id;

    Qualificacao::create($validatedData);

    return redirect()->back()->with('success', 'Qualificação cadastrada com sucesso');
  }

  /**
   * Atualiza qualificação
   *
   * @param Qualificacao $qualificacao
   * @param Request $request
   * @return RedirectResponse
   **/
  public function updateQualificacao(Request $request, Qualificacao $qualificacao): RedirectResponse
  {
    $validatedData = $request->validate([
      'ano' => 'nullable|string',
      'atividade' => 'nullable|string',
      'instrutor' => 'nullable|string',
    ], [
      'ano.string' => 'O campo Ano deve ser uma string.',
      'atividade.string' => 'O campo Atividade deve ser uma string.',
      'instrutor.string' => 'O campo Instrutor deve ser uma string.',
    ]);

    $qualificacao->update($validatedData);

    return redirect()->back()->with('success', 'Qualificação atualizada com sucesso');
  }

    /**
   * Remove avaliador
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function deleteQualificacao(Qualificacao $qualificacao): RedirectResponse
  {

    $qualificacao->delete();

    return redirect()->back()->with('warning', 'Qualificação removida');
  }

  /**
   * Adiciona area de atuação
   *
   * @param Avaliador $avaliador
   * @param Request $request
   * @return RedirectResponse
   **/
  public function createArea(Avaliador $avaliador, Request $request): RedirectResponse
  {
    $validatedData = $request->validate([
      'area_id' => ['required', 'exists:areas_atuacao,id'],
      'situacao' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
      'data_cadastro' => ['nullable', 'date'],
    ], [
      'area_id.required' => 'Selecione uma opção válida',
      'area_id.exists' => 'Selecione uma opção válida',
      'situacao.in' => 'Selecione uma opção válida',
      'data_cadastro.date' => 'Data inválida'
    ]);

    if (!$avaliador) {
      return redirect()->back()->with('error', 'Houve um erro, tente novamente');
    }

    $validatedData['avaliador_id'] = $avaliador->id;

    AvaliadorArea::create($validatedData);

    return redirect()->back()->with('success', 'Área cadastrada com sucesso');
  }

  /**
   * Atualiza qualificação
   *
   * @param AvaliadorArea $area
   * @param Request $request
   * @return RedirectResponse
   **/
  public function updateArea(Request $request, AvaliadorArea $area): RedirectResponse
  {
    $validatedData = $request->validate([
      'area_id' => ['required', 'exists:areas_atuacao,id'],
      'situacao' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
      'data_cadastro' => ['nullable', 'date'],
    ], [
      'area_id.required' => 'Selecione uma opção válida',
      'area_id.exists' => 'Selecione uma opção válida',
      'situacao.in' => 'Selecione uma opção válida',
      'data_cadastro.date' => 'Data inválida'
    ]);

    $area->update($validatedData);

    return redirect()->back()->with('success', 'Área atualizada com sucesso');
  }

  /**
   * Remove avaliador
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function deleteArea(AvaliadorArea $area): RedirectResponse
  {
    $area->delete();

    return redirect()->back()->with('warning', 'Qualificação removida');
  }

  /**
   * Adiciona certificado
   *
   * @param Avaliador $avaliador
   * @param Request $request
   * @return RedirectResponse
   **/
  public function createCertificado(Avaliador $avaliador, Request $request): RedirectResponse
  {
    $validatedData = $request->validate([
      'data' => ['nullable', 'date'],
      'revisao' => ['nullable', 'string'],
      'responsavel' => ['nullable', 'string'],
      'motivo' => ['nullable', 'string'],
    ], [
      'data.date' => ' Data inválida',
      'revisao.string' => 'Dado inválido',
      'responsavel.string' => 'Dado inválido',
      'motivo.string' => 'Dado inválido',
    ]);

    if (!$avaliador) {
      return redirect()->back()->with('error', 'Houve um erro, tente novamente');
    }

    $validatedData['avaliador_id'] = $avaliador->id;

    CertificadoAvaliador::create($validatedData);

    return redirect()->back()->with('success', 'Certificado cadastrado com sucesso');
  }

  /**
   * Atualiza certificado
   *
   * @param CertificadoAvaliador $certificado
   * @param Request $request
   * @return RedirectResponse
   **/
  public function updateCertificado(Request $request, CertificadoAvaliador $certificado): RedirectResponse
  {
    $validatedData = $request->validate([
      'data' => ['nullable', 'date'],
      'revisao' => ['nullable', 'string'],
      'responsavel' => ['nullable', 'string'],
      'motivo' => ['nullable', 'string'],
    ], [
      'data.date' => ' Data inválida',
      'revisao.string' => 'Dado inválido',
      'responsavel.string' => 'Dado inválido',
      'motivo.string' => 'Dado inválido',
    ]);

    $certificado->update($validatedData);

    return redirect()->back()->with('success', 'Certificado atualizado com sucesso');
  }

    /**
   * Remove certificado
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function deleteCertificado(CertificadoAvaliador $certificado): RedirectResponse
  {
    $certificado->delete();

    return redirect()->back()->with('warning', 'Certificado removido');
  }

  /**
   * Adiciona status
   *
   * @param Avaliador $avaliador
   * @param Request $request
   * @return RedirectResponse
   **/
  public function createStatus(Avaliador $avaliador, Request $request): RedirectResponse
  {
    $validatedData = $request->validate([
      'data' => ['nullable', 'date'],
      'status' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
      'parecer_positivo' => ['nullable', 'in:0,1'],
      'seminario' => ['nullable', 'in:0,1'],
    ], [
      'data.date' => ' Data inválida',
      'status.in' => 'Selecione uma opção válida',
      'parecer_positivo.in' => 'Selecione uma opção válida',
      'seminario.in' => 'Selecione uma opção válida',
    ]);

    if (!$avaliador) {
      return redirect()->back()->with('error', 'Houve um erro, tente novamente');
    }

    $validatedData['avaliador_id'] = $avaliador->id;

    StatusAvaliador::create($validatedData);

    return redirect()->back()->with('success', 'Status cadastrado com sucesso');
  }

  /**
   * Atualiza status
   *
   * @param StatusAvaliador $status
   * @param Request $request
   * @return RedirectResponse
   **/
  public function updateStatus(Request $request, StatusAvaliador $status): RedirectResponse
  {
    $validatedData = $request->validate([
      'data' => ['nullable', 'date'],
      'status' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
      'parecer_positivo' => ['nullable', 'in:0,1'],
      'seminario' => ['nullable', 'in:0,1'],
    ], [
      'data.date' => ' Data inválida',
      'status.in' => 'Selecione uma opção válida',
      'parecer_positivo.in' => 'Selecione uma opção válida',
      'seminario.in' => 'Selecione uma opção válida',
    ]);

    $status->update($validatedData);

    return redirect()->back()->with('success', 'Status atualizado com sucesso');
  }

  /**
   * Remove status
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function deleteStatus(StatusAvaliador $status): RedirectResponse
  {
    $status->delete();

    return redirect()->back()->with('warning', 'Status removido');
  }




}
