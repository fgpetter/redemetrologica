<?php

namespace App\Http\Controllers;

use App\Models\Interlab;
use Illuminate\Http\Request;
use App\Models\AgendaInterlab;
use App\Models\MaterialPadrao;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\InterlabMateriaisPadrao;

class AgendaInterlabController extends Controller
{
   /**
   * Gera tela de lista de cursos agendados
   * 
   * @return View
   */
  public function index(): View
  {
    $agenda_interlabs = AgendaInterlab::with('interlab')->paginate(15);
    return view('painel.agenda-interlab.index', ['agenda_interlabs' => $agenda_interlabs]);
  }

   /**
   * Tela de cadastro e ediçao de agenda de cursos
   * 
   * @param AgendaCursos $agendamento_curso
   * @return View
   */
  public function insert(AgendaInterlab $agendainterlab): View
  {

    $data = [
      'agendainterlab' => $agendainterlab , 
      'interlabs' => Interlab::all(),
      'materiaisPadrao' => MaterialPadrao::whereIn('tipo', ['INTERLAB', 'AMBOS'])->get(),
      'interlabMateriaisPadrao' => $agendainterlab->materiaisPadrao()->get(),
    ];

    return view('painel.agenda-interlab.insert', $data);
  }

  /**
   * Adiciona agenda interlab na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $validated = $request->validate(
      [
        'interlab_id' => ['required', 'numeric', 'exists:interlabs,id'],
        'status' => ['required', 'string', 'in:APROVADO,PENDENTE,REPROVADO'],
        'tipo' => ['required', 'string', 'in:BILATERAL,INTERLABORATORIAL'],
        'inscricao' => ['nullable', 'numeric'],
        'site' => ['nullable', 'numeric'],
        'destaque' => ['nullable', 'numeric'],
        'descricao' => ['nullable', 'string'],
        'data_inicio' => ['required', 'date'],
        'data_fim' => ['nullable', 'date'],
        'sob_demanda' => ['nullable', 'numeric'],
      ],
      [
        'interlab_id.required' => 'Selecione um interlab',
        'interlab_id.exists' => 'Opção inválida',
        'interlab_id.numeric' => 'Opção inválida',
        'status.required' => 'O campo status obrigatório',
        'status.in' => 'Opção inválida',
        'status.string' => 'Permitido somente texto',
        'tipo.required' => 'O campo tipo obrigatório',
        'tipo.in' => 'Opção inválida',
        'tipo.string' => 'Permitido somente texto',
        'inscricao.numeric' => 'Opção inválida',
        'site.numeric' => 'Opção inválida',
        'destaque.numeric' => 'Opção inválida',
        'descricao.string' => 'Permitido somente texto',
        'data_inicio.required' => 'O campo data obrigatório',
        'data_inicio.date' => 'Permitido somente data',
        'data_fim.date' => 'Permitido somente data',
        'sob_demanda.numeric' => 'Opção inválida',
      ]
    );

    $agenda_interlab = AgendaInterlab::create($validated);

    if (!$agenda_interlab) {
      return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->back()->with('success', 'Agenda interlab cadastrado com sucesso');
  }

  /**
   * Altera agenda interlab
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function update(Request $request, AgendaInterlab $agendainterlab): RedirectResponse
  {
    $validated = $request->validate(
      [
        'interlab_id' => ['required', 'numeric', 'exists:interlabs,id'],
        'status' => ['required', 'string', 'in:APROVADO,PENDENTE,REPROVADO'],
        'tipo' => ['required', 'string', 'in:BILATERAL,INTERLABORATORIAL'],
        'inscricao' => ['nullable', 'numeric'],
        'site' => ['nullable', 'numeric'],
        'destaque' => ['nullable', 'numeric'],
        'descricao' => ['nullable', 'string'],
        'data_inicio' => ['required', 'date'],
        'data_fim' => ['nullable', 'date'],
        'sob_demanda' => ['nullable', 'numeric'],
      ],
      [
        'interlab_id.required' => 'Selecione um interlab',
        'interlab_id.exists' => 'Opção inválida',
        'interlab_id.numeric' => 'Opção inválida',
        'status.required' => 'O campo status obrigatório',
        'status.in' => 'Opção inválida',
        'status.string' => 'Permitido somente texto',
        'tipo.required' => 'O campo tipo obrigatório',
        'tipo.in' => 'Opção inválida',
        'tipo.string' => 'Permitido somente texto',
        'inscricao.numeric' => 'Opção inválida',
        'site.numeric' => 'Opção inválida',
        'destaque.numeric' => 'Opção inválida',
        'descricao.string' => 'Permitido somente texto',
        'data_inicio.required' => 'O campo data obrigatório',
        'data_inicio.date' => 'Permitido somente data',
        'data_fim.date' => 'Permitido somente data',
        'sob_demanda.numeric' => 'Opção inválida',
      ]
    );

    $agendainterlab->update($validated);

    return redirect()->back()->with('success', 'Agenda interlab atualizado com sucesso');
  }

  /**
   * Remove interlab
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function delete(AgendaInterlab $agendainterlab): RedirectResponse
  {
    $agendainterlab->delete();

    return redirect()->route('agenda-interlab-index')->with('warning', 'Agenda interlab removido');
  }

  /**
   * Salva despesa do agendamento de curso
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function salvaMaterialPadrao(Request $request): RedirectResponse
  {
    $request->validate([
      'agenda_interlab_id' => ['nullable', 'exists:agenda_interlabs,id'],
      'material_padrao' => ['required', 'exists:materiais_padroes,id'],
      'quantidade' => ['required', 'integer'],
      'valor' => ['required','regex:/[\d.,]+$/'],
      'total' => ['required', 'regex:/[\d.,]+$/'],
      'lote' => ['nullable', 'string'],
      'validade' => ['nullable', 'date'],
      'data_compra' => ['nullable', 'date'],
    ],[
      'agenda_interlab_id.exists' => 'Houve um erro ao editar despesa. Tente novamente',
      'material_padrao.exists' => 'Selecione uma opção válida',
      'quantidade.integer' => 'O dado enviado não é valido',
      'quantidade.required' => 'Preencha o campo',
      'valor.required' => 'Preencha o campo',
      'valor.regex' => 'Não é um número válido',
      'total.required' => 'O campo não pode estar vazio',
      'total.regex' => 'Não é um número válido',
      'lote.string' => 'Permitido somente texto',
      'validade.date' => 'Permitido somente data',
      'data_compra.date' => 'Permitido somente data',
    ]);

    InterlabMateriaisPadrao::updateOrCreate([
      'agenda_interlab_id' => $request->agenda_interlab_id,
      'material_padrao_id' => $request->material_padrao,
    ],[
      'quantidade' => $request->quantidade,
      'valor' => $this->formataMoeda($request->valor),
      'total' => $request->total,
      'lote' => $request->lote,
      'validade' => $request->validade,
      'data_compra' => $request->data_compra,
    ]);

    return back()->with('success', 'Material salvo com sucesso');
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
