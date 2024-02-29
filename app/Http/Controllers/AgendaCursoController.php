<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Pessoa;
use App\Models\Instrutor;
use App\Models\AgendaCursos;
use Illuminate\Http\Request;
use App\Models\CursoInscrito;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Models\CursoInscritoEmpresa;
use Illuminate\Http\RedirectResponse;

class AgendaCursoController extends Controller
{
  /**
   * Gera tela de lista de cursos agendados
   * 
   * @return View
   */
  public function index(): View
  {
    $data = [
      'agenda_cursos' => AgendaCursos::orderBy('data_inicio')->get()
    ];
    return view('painel.agendamento-cursos.index', $data);
  }

  /**
   * Tela de cadastro e ediçao de agenda de cursos
   * 
   * @param AgendaCursos $agendamento_curso
   * @return View
   */
  public function insert(AgendaCursos $agendacurso): View
  {
    $data = [
      'instrutores' => Instrutor::all(),
      'cursos' => Curso::all(),
      'empresas' => Pessoa::select('uid', 'nome_razao')->where('tipo_pessoa', 'PJ')->limit(50)->get(),
      'inscritos' => CursoInscrito::select()->with('empresa')->where('agenda_curso_id', $agendacurso->id)->get(),
      'inscritos_empresas' => CursoInscritoEmpresa::select()->where('agenda_curso_id', $agendacurso->id)->get(),
      'agendacurso' => $agendacurso
    ];

    foreach ($data['inscritos_empresas'] as $key => $empresa) {
      $participantes = CursoInscrito::select('empresa_id')->where('empresa_id', $empresa->pessoa_id)->count();
      $data['inscritos_empresas'][$key]['participantes'] = $participantes;
    }

    return view('painel.agendamento-cursos.insert', $data);
  }

  /**
   * Adiciona um agendamento de curso
   * 
   * @param Request $request
   * @return RedirectResponse
   */
  public function create(Request $request): RedirectResponse
  {
    $validated = $request->validate([
      'status' => ['required', Rule::in(['AGENDADO', 'CANCELADO', 'CONFIRMADO', 'REALIZADO', 'PROPOSTA ENVIADA', 'REAGENDAR'])],
      'status_proposta' => ['nullable', Rule::in(['PENDENTE', 'AGUARDANDO APROVACAO', 'APROVADA', 'REPROVADA'])],
      'destaque' => ['nullable', 'integer'],
      'tipo_agendamento' => ['required', Rule::in(['ONLINE', 'EVENTO', 'IN-COMPANY'])],
      'curso_id' => ['required', 'exists:cursos,id'],
      'instrutor_id' => ['required', 'exists:instrutores,id'],
      'pessoa_id' => ['nullable', 'exists:pessoas,id'],
      'endereco_local' => ['nullable', 'string'],
      'data_inicio' => ['required', 'date'],
      'data_fim' => ['nullable', 'date'],
      'data_limite_pagamento' => ['nullable', 'date'],
      'validade_proposta' => ['nullable', 'date'],
      'horario' => ['nullable', 'string'],
      'inscricoes' => ['nullable', 'integer'],
      'site' => ['nullable', 'integer'],
      'num_participantes' => ['nullable', 'integer'],
      'carga_horaria' => ['nullable', 'integer'],
      'investimento' => ['nullable', 'string'],
      'investimento_associado' => ['nullable', 'string'],
      'observacoes' => ['nullable', 'string'],
      'contato' => ['nullable', 'string'],
      'contato_email' => ['nullable', 'string'],
      'contato_telefone' => ['nullable', 'string'],
      'valor_orcamento' => ['nullable', 'string'],

    ], [
      'status.required' => 'Selecione uma opção válida',
      'status.in' => 'Selecione uma opção válida',
      'status_proposta.in' => 'Selecione uma opção válida',
      'destaque.integer' => 'O dado enviado não é valido',
      'tipo_agendamento.in' => 'Selecione uma opção válida',
      'curso_id.required' => 'É necessário escolher um curso',
      'curso_id.exists' => 'Selecione uma opção válida',
      'pessoa_id.exists' => 'Selecione uma opção válida',
      'instrutor_id.required' => 'É necessário escolher um instrutor',
      'instrutor_id.in' => 'Selecione uma opção válida',
      'endereco_local.string' => 'O dado enviado não é valido',
      'data_inicio.date' => 'O dado enviado não é uma data valida',
      'data_fim.date' => 'O dado enviado não é uma data valida',
      'data_limite_pagamento.date' => 'O dado enviado não é uma data valida',
      'validade_proposta.date' => 'O dado enviado não é uma data valida',
      'horario.string' => 'O dado enviado não é valido',
      'inscricoes.integer' => 'O dado enviado não é valido',
      'site.integer' => 'O dado enviado não é valido',
      'num_participantes.integer' => 'O dado enviado não é valido',
      'carga_horaria.integer' => 'O dado enviado não é valido',
      'investimento.string' => 'O dado enviado não é valido',
      'investimento_associado.string' => 'O dado enviado não é valido',
      'contato.string' => 'O dado enviado não é valido',
      'contato_email.string' => 'O dado enviado não é valido',
      'contato_telefone.string' => 'O dado enviado não é valido',
      'valor_orcamento.string' => 'O dado enviado não é valido',
      'observacoes.string' => 'O dado enviado não é valido',
    ]);
    $validated['uid'] = config('hashing.uid');
    $validated['investimento'] = ($validated['investimento']) ? str_replace(',','.', str_replace('.','', $request->investimento)) : null;
    $validated['investimento_associado'] = ($validated['investimento_associado']) ? str_replace(',','.', str_replace('.','', $request->investimento_associado)) : null;

    $agenda_curso = AgendaCursos::create($validated);

    if (!$agenda_curso) {
      return back()->with('agendamento-error', 'Houve um erro, tente novamente');
    }

    return redirect()->route('agendamento-curso-index')
      ->with('agendamento-success', 'Agendamento cadastrado com sucesso');
  }

  /**
   * Atualiza dados de agenda de cursos
   * 
   * @param AgendaCursos $agendacurso
   * @param Request $request
   * @return RedirectResponse
   */
  public function update(AgendaCursos $agendacurso, Request $request): RedirectResponse
  {
    $validated = $request->validate([
      'status' => ['required', Rule::in(['AGENDADO', 'CANCELADO', 'CONFIRMADO', 'REALIZADO', 'PROPOSTA ENVIADA', 'REAGENDAR'])],
      'status_proposta' => ['nullable', Rule::in(['PENDENTE', 'AGUARDANDO APROVACAO', 'APROVADA', 'REPROVADA'])],
      'destaque' => ['nullable', 'integer'],
      'tipo_agendamento' => ['required', Rule::in(['ONLINE', 'EVENTO', 'IN-COMPANY'])],
      'curso_id' => ['required', 'exists:cursos,id'],
      'instrutor_id' => ['required', 'exists:instrutores,id'],
      'pessoa_id' => ['nullable', 'exists:pessoas,id'],
      'endereco_local' => ['nullable', 'string'],
      'data_inicio' => ['required', 'date'],
      'data_fim' => ['nullable', 'date'],
      'data_limite_pagamento' => ['nullable', 'date'],
      'validade_proposta' => ['nullable', 'date'],
      'horario' => ['nullable', 'string'],
      'inscricoes' => ['nullable', 'integer'],
      'site' => ['nullable', 'integer'],
      'num_participantes' => ['nullable', 'integer'],
      'carga_horaria' => ['nullable', 'integer'],
      'investimento' => ['nullable', 'string'],
      'investimento_associado' => ['nullable', 'string'],
      'observacoes' => ['nullable', 'string'],
      'contato' => ['nullable', 'string'],
      'contato_email' => ['nullable', 'string'],
      'contato_telefone' => ['nullable', 'string'],
      'valor_orcamento' => ['nullable', 'string'],

    ], [
      'status.required' => 'Selecione uma opção válida',
      'status.in' => 'Selecione uma opção válida',
      'status_proposta.in' => 'Selecione uma opção válida',
      'destaque.integer' => 'O dado enviado não é valido',
      'tipo_agendamento.in' => 'Selecione uma opção válida',
      'curso_id.required' => 'Selecione uma opção válida',
      'curso_id.exists' => 'Selecione uma opção válida',
      'pessoa_id.exists' => 'Selecione uma opção válida',
      'instrutor_id.required' => 'Selecione uma opção válida',
      'instrutor_id.in' => 'Selecione uma opção válida',
      'endereco_local.string' => 'O dado enviado não é valido',
      'data_inicio.date' => 'O dado enviado não é uma data valida',
      'data_fim.date' => 'O dado enviado não é uma data valida',
      'data_limite_pagamento.date' => 'O dado enviado não é uma data valida',
      'validade_proposta.date' => 'O dado enviado não é uma data valida',
      'horario.string' => 'O dado enviado não é valido',
      'inscricoes.integer' => 'O dado enviado não é valido',
      'site.integer' => 'O dado enviado não é valido',
      'num_participantes.integer' => 'O dado enviado não é valido',
      'carga_horaria.integer' => 'O dado enviado não é valido',
      'investimento.string' => 'O dado enviado não é valido',
      'investimento_associado.string' => 'O dado enviado não é valido',
      'contato.string' => 'O dado enviado não é valido',
      'contato_email.string' => 'O dado enviado não é valido',
      'contato_telefone.string' => 'O dado enviado não é valido',
      'valor_orcamento.string' => 'O dado enviado não é valido',
      'observacoes.string' => 'O dado enviado não é valido',
    ]);
    $validated['investimento'] = ($validated['investimento']) ? str_replace(',','.', str_replace('.','', $request->investimento)) : null;
    $validated['investimento_associado'] = ($validated['investimento_associado']) ? str_replace(',','.', str_replace('.','', $request->investimento_associado)) : null;

    $agendacurso->update($validated);

    return redirect()->route('agendamento-curso-index')
      ->with('agendamento-success', 'Agendamento atualizado com sucesso');
  }

  /**
   * Remove um agendamento de curso
   * 
   * @param AgendaCursos $agendacurso
   * @return RedirectResponse
   */
  public function delete(AgendaCursos $agendacurso): RedirectResponse
  {
    $agendacurso->delete();

    return redirect()->route('agendamento-curso-index')
      ->with('agendamento-success', 'Agendamento removido com sucesso');
  }

  /**
   * Gera pagina de listagem de cursos agendados no site
   * @return View
   **/
  public function listCursosAgendados(): View
  {
    $agendacursos = AgendaCursos::select()
      ->whereIn('status', ['AGENDADO', 'CONFIRMADO'])
      ->whereNotIn('tipo_agendamento', ['IN-COMPANY'])
      ->where('site', 1)
      ->whereNotNull('data_inicio')
      ->with('curso')
      ->get();

    return view('site.pages.cursos', ['agendacursos' => $agendacursos]);
  }


  /**
   * mostra pagina da slug de cursos agendados
   *
   
   * @return View
   **/
  public function showCursoAgendado($uid): View
  {

    $agendacursos = Agendacursos::where('uid', $uid)->with('curso')->first();


    return view('site.pages.slug-cursos', ['agendacursos' => $agendacursos]);
  }
}
