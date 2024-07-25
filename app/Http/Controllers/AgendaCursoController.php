<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Pessoa;
use App\Models\Instrutor;
use App\Models\AgendaCursos;
use App\Models\CursoDespesa;
use Illuminate\Http\Request;
use App\Models\CursoInscrito;
use App\Models\MaterialPadrao;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
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
      'instrutores' => Instrutor::select('id','uid', 'pessoa_id')->with('pessoa')->whereNot('id', $agendacurso->instrutor_id)->get(),
      'instrutor_atual' => Instrutor::select('id', 'uid', 'pessoa_id')->with(['pessoa' => fn($q) => $q->withTrashed()])
        ->where('id', $agendacurso->instrutor_id)
        ->withTrashed()
        ->first(),

      'cursos' => Curso::select('id', 'descricao')->whereNot('id', $agendacurso->curso_id)->get(),
      'curso_atual' => Curso::select('id', 'descricao')->where('id', $agendacurso->curso_id)->withTrashed()->first(),
      'empresas' => Pessoa::select('uid', 'nome_razao')->where('tipo_pessoa', 'PJ')->get(),
      'inscritos' => CursoInscrito::select()->with('empresa')->with('pessoa')->where('agenda_curso_id', $agendacurso->id)->get(),
      'despesas' => $agendacurso->despesas()->with('materialPadrao:id,descricao')->get(),
      'materiaispadrao' => MaterialPadrao::select('id', 'descricao')->whereiN('tipo', ['CURSOS', 'AMBOS'])->get(),
      'agendacurso' => $agendacurso
    ];

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
      'empresa_id' => ['nullable', 'exists:pessoas,id'],
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
      'empresa_id.exists' => 'Selecione uma opção válida',
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

    $validated['investimento'] = $this->formataMoeda($validated['investimento']) ?? null;
    $validated['investimento_associado'] = $this->formataMoeda($validated['investimento_associado']) ?? null;
    $validated['valor_orcamento'] = $this->formataMoeda($validated['valor_orcamento']) ?? null;

    $agenda_curso = AgendaCursos::create($validated);

    if (!$agenda_curso) {
      return back()->with('agendamento-error', 'Houve um erro, tente novamente');
    }

    return redirect()->route('agendamento-curso-index')
      ->with('success', 'Agendamento cadastrado com sucesso');
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
      'instrutor_id.in' => 'Instrutor não cadastrado',
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

    $validated['investimento'] = $this->formataMoeda($validated['investimento']) ?? null;
    $validated['investimento_associado'] = $this->formataMoeda($validated['investimento_associado']) ?? null;
    $validated['valor_orcamento'] = $this->formataMoeda($validated['valor_orcamento']) ?? null;

    if (!$request->destaque) {
      $validated['destaque'] = 0;
    }
    if (!$request->site) {
      $validated['site'] = 0;
    }
    if (!$request->inscricoes) {
      $validated['inscricoes'] = 0;
    }

    $agendacurso->update($validated);

    return redirect()->route('agendamento-curso-index')
      ->with('success', 'Agendamento atualizado com sucesso');
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
      ->with('warning', 'Agendamento removido com sucesso');
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

  /**
   * Salva despesa do agendamento de curso
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function salvaDespesa(Request $request): RedirectResponse
  {
    $request->validate([
      'agenda_curso_id' => ['nullable', 'exists:agenda_cursos,id'],
      'material_padrao' => ['required', 'exists:materiais_padroes,id'],
      'quantidade' => ['required', 'regex:/[\d.,]+$/'],
      'valor' => ['required','regex:/[\d.,]+$/'],
      'total' => ['required', 'regex:/[\d.,]+$/'],
    ],[
      'agenda_curso_id.exists' => 'Houve um erro ao editar despesa. Tente novamente',
      'material_padrao.exists' => 'Selecione uma opção válida',
      'quantidade.regex' => 'O dado enviado não é valido',
      'quantidade.required' => 'Preencha o campo',
      'valor.required' => 'Preencha o campo',
      'valor.regex' => 'Não é um número válido',
      'total.required' => 'O campo não pode estar vazio',
      'total.regex' => 'Não é um número válido',
    ]);


    CursoDespesa::updateOrCreate([
      'agenda_cursos_id' => $request->agenda_curso_id,
      'material_padrao_id' => $request->material_padrao,
    ],[
      'quantidade' => $request->quantidade,
      'valor' => $this->formataMoeda($request->valor),
      'total' => $request->total,
    ]);

    return back()->with('success', 'Despesa salva com sucesso');
  }

  /**
   * Formata valor para BD
   *
   * @param string $valor
   * @return string|null
   */
  private function formataMoeda($valor): ?string
  {
    if ($valor) {
      return str_replace(',', '.', str_replace('.', '', $valor));
    } else {
      return null;
    }
  }
}
