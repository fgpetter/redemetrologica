<?php

namespace App\Http\Controllers;

use App\Models\Interlab;
use App\Models\Parametro;
use Illuminate\Http\Request;
use App\Models\AgendaInterlab;
use App\Models\MaterialPadrao;
use App\Models\InterlabDespesa;
use App\Models\InterlabParametro;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;


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
      'interlabDespesa' => $agendainterlab->despesas()->get(),
      'fabricantes' => DB::table('interlab_despesas')->distinct()->get(['fabricante']),
      'fornecedores' => DB::table('interlab_despesas')->distinct()->get(['fornecedor']),
      'interlabParametros' => $agendainterlab->parametros()->get(),
      'parametros' => Parametro::all()
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
  public function salvaDespesa(Request $request): RedirectResponse
  {
    $request->validate([
      'agenda_interlab_id' => ['nullable', 'exists:agenda_interlabs,id'],
      'despesa_id' => ['nullable', 'exists:interlab_despesas,id'],
      'material_padrao' => ['required', 'exists:materiais_padroes,id'],
      'quantidade' => ['nullable', 'regex:/[\d.,]+$/'],
      'valor' => ['nullable','regex:/[\d.,]+$/'],
      'total' => ['nullable', 'regex:/[\d.,]+$/'],
      'lote' => ['nullable', 'string'],
      'validade' => ['nullable', 'date'],
      'data_compra' => ['nullable', 'date'],
      'fornecedor' => ['nullable', 'string'],
      'fabricante' => ['nullable', 'string'],
      'cod_fabricante' => ['nullable', 'string'],
    ],[
      'agenda_interlab_id.exists' => 'Houve um erro ao editar despesa. Tente novamente',
      'despesa_id.exists' => 'Houve um erro ao editar despesa. Tente novamente',
      'material_padrao.exists' => 'Selecione uma opção válida',
      'quantidade.regex' => 'O dado enviado não é valido',
      'valor.regex' => 'Não é um número válido',
      'total.regex' => 'Não é um número válido',
      'lote.string' => 'Permitido somente texto',
      'validade.date' => 'Permitido somente data',
      'data_compra.date' => 'Permitido somente data',
      'fornecedor.string' => 'O dado enviado não é valido',
      'fabricante.string' => 'O dado enviado não é valido',
      'cod_fabricante.string' => 'O dado enviado não é valido',
    ]);

    InterlabDespesa::updateOrCreate([
      'id' => $request->despesa_id,
    ],[
      'agenda_interlab_id' => $request->agenda_interlab_id,
      'material_padrao_id' => $request->material_padrao,
      'quantidade' => $request->quantidade,
      'valor' => $this->formataMoeda($request->valor),
      'total' => $this->formataMoeda($request->total),
      'lote' => $request->lote,
      'validade' => $request->validade,
      'data_compra' => $request->data_compra,
      'fornecedor' => $request->fornecedor,
      'fabricante' => $request->fabricante,
      'cod_fabricante' => $request->cod_fabricante,

    ]);

    return back()->with('success', 'Material salvo com sucesso');
  }

  /**
   * Remove despesa do agendamento de curso
   *
   * @param InterlabDespesa $despesa
   * @return RedirectResponse
   */
  public function deleteDespesa(InterlabDespesa $despesa): RedirectResponse
  {
    $despesa->delete();
    return back()->with('warning', 'Material removido');
  }

  /**
   * Salva parametro do agendamento de curso
   *
   * @param Request $request
   * @return RedirectResponse
   */

  public function salvaParametro(Request $request): RedirectResponse
  {
    $request->validate([
      'agenda_interlab_id' => ['required', 'exists:agenda_interlabs,id'],
      'parametro_id' => ['required', 'exists:parametros,id'],
    ],[
      'agenda_interlab_id.exists' => 'Dado informado está incorreto. Tente novamente',
      'parametro_id.exists' => 'Dado informado está incorreto. Tente novamente',
    ]);

    InterlabParametro::create([
      'agenda_interlab_id' => $request->agenda_interlab_id,
      'parametro_id' => $request->parametro_id,
    ]);

    return back()->with('success', 'Parametro salvo com sucesso');
  }

  /**
   * Remove parametro
   *
   * @param InterlabDespesa $despesa
   * @return RedirectResponse
   */
  public function deleteParametro(InterlabParametro $parametro): RedirectResponse
  {
    $parametro->delete();
    return back()->with('warning', 'Parametro removido');
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
      if(str_contains($valor, '.') && str_contains($valor, ',') ) {
        return str_replace(',', '.', str_replace('.', '', $valor));
      }

      if(str_contains($valor, '.') && !str_contains($valor, ',') ) {
        return $valor;
      }

      if(str_contains($valor, ',') && !str_contains($valor, '.') ){
        return str_replace(',', '.', $valor);
      }

    } else {
      return null;
    }
  }

}
