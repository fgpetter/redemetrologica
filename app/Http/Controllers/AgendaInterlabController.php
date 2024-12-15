<?php

namespace App\Http\Controllers;

use App\Models\Interlab;
use App\Models\Parametro;
use Illuminate\Http\Request;
use App\Models\AgendaInterlab;
use App\Models\InterlabRodada;
use App\Models\MaterialPadrao;
use Illuminate\Support\Carbon;
use App\Models\InterlabDespesa;
use App\Models\InterlabParametro;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Http\RedirectResponse;
use App\Models\InterlabRodadaParametro;
use Illuminate\Support\Facades\Validator;


class AgendaInterlabController extends Controller
{
   /**
   * Gera tela de lista de interlabs agendados
   * 
   * @return View
   */
  public function index(): View
  {
    $agenda_interlabs = AgendaInterlab::with('interlab')
      ->orderBy('data_inicio', 'asc')
      ->paginate(15);

    return view('painel.agenda-interlab.index', ['agenda_interlabs' => $agenda_interlabs]);
  }

   /**
   * Tela de cadastro e ediçao de agenda de interlabs
   * 
   * @param AgendaInterlab $agendainterlab
   * @return View
   */
  public function insert(AgendaInterlab $agendainterlab): View
  {
    // loads despesas from agendainterlab
    $agendainterlab->load('despesas');
    $agendainterlab->load('parametros');
    $agendainterlab->load('rodadas');

    $data = [
      'agendainterlab' => $agendainterlab, 
      'interlabs' => Interlab::all(),
      'materiaisPadrao' => MaterialPadrao::whereIn('tipo', ['INTERLAB', 'AMBOS'])->orderBy('descricao')->get(),
      'interlabDespesa' => $agendainterlab->despesas,
      'fabricantes' => DB::table('interlab_despesas')->distinct()->get(['fabricante']),
      'fornecedores' => DB::table('interlab_despesas')->distinct()->get(['fornecedor']),
      'interlabParametros' => $agendainterlab->parametros,
      'parametros' => Parametro::orderBy('descricao')->get(),
      'rodadas' => $agendainterlab->rodadas,
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

    $validator = Validator::make($request->all(), [
      'interlab_id' => ['required', 'numeric', 'exists:interlabs,id'],
      'status' => ['required', 'string', 'in:AGENDADO,CONFIRMADO,CONCLUIDO'],
      'inscricao' => ['nullable', 'numeric'],
      'site' => ['nullable', 'numeric'],
      'destaque' => ['nullable', 'numeric'],
      'descricao' => ['nullable', 'string'],
      'data_inicio' => ['required', 'date'],
      'data_fim' => ['nullable', 'date'],
      'valor_rs' => ['nullable', 'string'],
      'valor_s_se' => ['nullable', 'string'],
      'valor_co' => ['nullable', 'string'],
      'valor_n_ne' => ['nullable', 'string'],

      ], [
      'interlab_id.required' => 'Selecione um interlab',
      'interlab_id.exists' => 'Opção inválida',
      'interlab_id.numeric' => 'Opção inválida',
      'status.required' => 'O campo status obrigatório',
      'status.in' => 'Opção inválida',
      'status.string' => 'Permitido somente texto',
      'inscricao.numeric' => 'Opção inválida',
      'site.numeric' => 'Opção inválida',
      'destaque.numeric' => 'Opção inválida',
      'descricao.string' => 'Permitido somente texto',
      'data_inicio.required' => 'O campo data obrigatório',
      'data_inicio.date' => 'Permitido somente data',
      'data_fim.date' => 'Permitido somente data',
      'valor_rs.string' => 'Valor inválido', 
      'valor_s_se.string' => 'Valor inválido', 
      'valor_co.string' => 'Valor inválido', 
      'valor_n_ne.string' => 'Valor inválido', 

    ]);

    if ($validator->fails()) {
      return back()
      ->withErrors($validator, 'principal')
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }

    $validated = $validator->validated();

    $validated['site'] = $request->site ?? 0;
    $validated['destaque'] = $request->destaque ?? 0;
    $validated['inscricao'] = $request->inscricao ?? 0;
    $validated['valor_rs'] = $this->formataMoeda($request->valor_s_se);
    $validated['valor_s_se'] = $this->formataMoeda($request->valor_s_se);
    $validated['valor_co'] = $this->formataMoeda($request->valor_co);
    $validated['valor_n_ne'] = $this->formataMoeda($request->valor_n_ne);
    $validated['descricao'] = $this->salvaImagensTemporarias($request);

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

    $validator = Validator::make($request->all(), [
      'interlab_id' => ['required', 'numeric', 'exists:interlabs,id'],
      'status' => ['required', 'string', 'in:AGENDADO,CONFIRMADO,CONCLUIDO'],
      'inscricao' => ['nullable', 'numeric'],
      'site' => ['nullable', 'numeric'],
      'destaque' => ['nullable', 'numeric'],
      'descricao' => ['nullable', 'string'],
      'data_inicio' => ['required', 'date'],
      'data_fim' => ['nullable', 'date'],
      'valor_rs' => ['nullable', 'string'],
      'valor_s_se' => ['nullable', 'string'],
      'valor_co' => ['nullable', 'string'],
      'valor_n_ne' => ['nullable', 'string'],
    ],[
      'interlab_id.required' => 'Selecione um interlab',
      'interlab_id.exists' => 'Opção inválida',
      'interlab_id.numeric' => 'Opção inválida',
      'status.required' => 'O campo status obrigatório',
      'status.in' => 'Opção inválida',
      'status.string' => 'Permitido somente texto',
      'inscricao.numeric' => 'Opção inválida',
      'site.numeric' => 'Opção inválida',
      'destaque.numeric' => 'Opção inválida',
      'descricao.string' => 'Permitido somente texto',
      'data_inicio.required' => 'O campo data obrigatório',
      'data_inicio.date' => 'Permitido somente data',
      'data_fim.date' => 'Permitido somente data',
      'valor_rs.string' => 'Valor inválido',
      'valor_s_se.string' => 'Valor inválido',
      'valor_co.string' => 'Valor inválido',
      'valor_n_ne.string' => 'Valor inválido',

    ]);


    if ($validator->fails()) {

      logger()->warning('VALIDATION ERROR', [ 
        'METHOD' => __METHOD__, 
        'REQUEST' => $request->all(),
        'FAILED' => $validator->failed(),
      ]);

      return back()
      ->withErrors($validator, 'principal')
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }


    $validated = $validator->validated();

    $validated['site'] = $request->site ?? 0;
    $validated['destaque'] = $request->destaque ?? 0;
    $validated['inscricao'] = $request->inscricao ?? 0;
    $validated['valor_rs'] = $this->formataMoeda($request->valor_s_se);
    $validated['valor_s_se'] = $this->formataMoeda($request->valor_s_se);
    $validated['valor_co'] = $this->formataMoeda($request->valor_co);
    $validated['valor_n_ne'] = $this->formataMoeda($request->valor_n_ne);
    $validated['descricao'] = $this->salvaImagensTemporarias($request);

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
   * Salva despesa do agendamento de interlab
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function salvaDespesa(Request $request): RedirectResponse
  {

    $validator = Validator::make($request->all(), [
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
      'agenda_interlab_id.exists' => 'O campo descrição não tem um material válido',
      'despesa_id.exists' => 'Você está tentando editar uma despesa que nao existe',
      'material_padrao.exists' => 'Selecione uma opção de material ou padrão válida',
      'quantidade.regex' => 'Quantidade não é um número válido',
      'valor.regex' => 'Valor não é um número válido',
      'total.regex' => 'Valor total não é um número válido',
      'lote.string' => 'Iformação de lote não é valida',
      'validade.date' => 'Validade permite somente data',
      'data_compra.date' => 'Data de compra permite somente data',
      'fornecedor.string' => 'Nome de fornecedor não é um texto válido',
      'fabricante.string' => 'Nome de fabricante não é um texto válido',
      'cod_fabricante.string' => 'Código de fornecedor não é um texto válido',

    ]);

    if ($validator->fails()) {

      logger()->warning('VALIDATION ERROR', [ 
        'METHOD' => __METHOD__, 
        'REQUEST' => $request->all(),
        'FAILED' => $validator->failed(),
      ]);

      return back()
      ->withErrors($validator, 'despesas')
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente')
      ->withFragment('despesas');
    }

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

    return back()->with('success', 'Material salvo com sucesso')->withFragment('despesas');
  }

/**
 * Duplica despesa do agendamento de interlab removendo campos 
 * que não devem sere duplicados como id, uid, and timestamps.
 *
 * @param InterlabDespesa $despesa
 * @return \Illuminate\Http\RedirectResponse
 */
  public function duplicarDespesa(InterlabDespesa $despesa): RedirectResponse 
  {
    $despesa = collect($despesa)->forget(['id','uid', 'created_at', 'updated_at', 'deleted_at'])->toArray();

    $despesa['validade'] = ($despesa['validade']) ? Carbon::parse($despesa['validade'])->format('Y-m-d') : null;
    $despesa['data_compra'] = ($despesa['data_compra']) ? Carbon::parse($despesa['data_compra'])->format('Y-m-d') : null;

    InterlabDespesa::create($despesa);

    return back()->with('success', 'Material duplicado com sucesso')->withFragment('despesas');
  }

  /**
   * Remove despesa do agendamento de interlab
   *
   * @param InterlabDespesa $despesa
   * @return RedirectResponse
   */
  public function deleteDespesa(InterlabDespesa $despesa): RedirectResponse
  {
    $despesa->delete();
    return back()->with('warning', 'Material removido')->withFragment('despesas');
  }

  /**
   * Salva rodada
   *
   * @param Request $request
   * @return RedirectResponse
   */

  public function salvaRodada(Request $request): RedirectResponse
  {
    $validator = Validator::make($request->all(), 
      [
        'agenda_interlab_id' => ['required', 'exists:agenda_interlabs,id'],
        'rodada_id' => ['nullable', 'exists:interlab_rodadas,id'],
        'descricao' => ['required', 'string'],
        'vias' => ['nullable', 'numeric' ,'min:1'],
        'cronograma' => ['nullable', 'string'],
        'parametros' => ['nullable', 'array'],
        'parametros.*' => ['nullable', 'exists:parametros,id'],
      ], [
        'agenda_interlab_id.required' => 'Houve um erro ao salvar. Agenda inexistente',
        'agenda_interlab_id.exists' => 'Houve um erro ao salvar. Agenda inexistente',
        'rodada_id.exists' => 'Houve um erro ao salvar. Rodada inexistente',
        'descricao.required' => 'O campo descricão obrigatório',
        'descricao.string' => 'O campo descricão permite somente texto',
        'vias.numeric' => 'O campo vias deve ser um número',
        'vias.min' => 'O campo vias deve ser maior que 0',
        'cronograma.string' => 'O campo cronograma permite somente texto',
        'parametros.array' => 'Houve um erro ao salvar. Parametros inválidos',
        'parametros.*.exists' => 'O parametro :input não existe',
      ]
    );

    if ($validator->fails()){
      return back()
        ->withErrors($validator, 'rodadas')
        ->withInput()
        ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }


    $interlab_rodada = InterlabRodada::updateOrCreate([
      'id' => $request->rodada_id,
    ],[
      'agenda_interlab_id' => $request->agenda_interlab_id,
      'descricao' => $request->descricao,
      'vias' => $request->vias,
      'cronograma' => $request->cronograma,
    ]);

    $interlab_rodada->updateParametros($request->parametros);

    return back()->with('success', 'Rodada salva com sucesso');
  }

  /**
   * Remove rodada
   *
   * @param InterlabRodada $rodada
   * @return RedirectResponse
   */
  public function deleteRodada(InterlabRodada $rodada): RedirectResponse
  {
    $rodada->delete();
    return back()->with('warning', 'Rodada removida');
  }

  public function salvaParametro(Request $request): RedirectResponse {
    
    $validator = Validator::make($request->all(),[
      'parametro_id' => ['required', 'exists:parametros,id'],
      'agenda_interlab_id' => ['required', 'exists:agenda_interlabs,id'],
    ],[
      'parametro_id.required' => 'O parametro é obrigatório',
      'parametro_id.exists' => 'O parametro selecionado não existe',
      'agenda_interlab_id.required' => 'Você está tentando editar um agendamento que não existe',
      'agenda_interlab_id.exists' => 'Você está tentando editar um agendamento que não existe',
    ]);

    if ($validator->fails()) {

      logger()->warning('VALIDATION ERROR', [ 
        'METHOD' => __METHOD__, 
        'REQUEST' => $request->all(),
        'FAILED' => $validator->failed(),
      ]);

      return back()
      ->withErrors($validator, 'despesas')
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }

    InterlabParametro::updateOrCreate([
      'agenda_interlab_id' => $request->agenda_interlab_id,
      'parametro_id' => $request->parametro_id,
    ]);

    return back()->with('success', 'Parâmetro salvo com sucesso')->withFragment('despesas');
  }

  public function deleteParametro(InterlabParametro $parametro, Request $request): RedirectResponse {

    $request->validate([
      'agenda_interlab_id' => ['required', 'exists:agenda_interlabs,id'],
      'parametro_id' => ['required', 'exists:parametros,id'],
    ]);

    InterlabRodadaParametro::where('agenda_interlab_id', $request->agenda_interlab_id)
      ->where('parametro_id', $request->parametro_id)
      ->delete();

    $parametro->delete();

    return back()->with('warning', 'Parâmetro removido')->withFragment('despesas');
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

  /**
   * 
   * Lida com imagens temporárias do editor de imagens 
   * e retorna o conteúdo atualizado para pasta correta
   * 
   * @return string $content
   */
  private function salvaImagensTemporarias($request): string {
    $descricao = $request->get('descricao');
    
    // lida com as pastas temporarias do editor de conteúdo
    if (session()->has('tempPastas')) {
      $tempPastas = session()->get('tempPastas');

      //troca a pasta temporaria pela permanente no conteudo do texto
      foreach ($tempPastas as $tempPasta) {
        $descricao = str_replace($tempPasta, 'interlab-media', $descricao);
      }

      foreach ($tempPastas as $tempPasta) {
        $tempMediaPath = public_path($tempPasta);
        $postMediaPath = public_path('interlab-media');

        if(File::exists($tempMediaPath)) {
          $files = File::allFiles($tempMediaPath);
  
          foreach ($files as $file) {
            $destinationPath = $postMediaPath . DIRECTORY_SEPARATOR . $file->getFilename();
  
            // copia se o arquivo não existir
            if (!File::exists($destinationPath)) {
              File::copy($file->getPathname(), $destinationPath);
            }
          }
          // deleta pasta temporaria
          File::deleteDirectory($tempMediaPath);
        }
      }

      // limpa a session
      session()->forget('tempPastas');
    }
    return $descricao;
  }

  /**
   * Exibe no site a lista de interlaboratoriais
   *
   * @return View
   */
  public function exibeInterlabsSite() {
    
    $interlabs = AgendaInterlab::with('interlab')
    ->where('site', 1)
    ->orderBy('destaque', 'desc')
    ->orderBy('inscricao', 'desc')
    ->get();

    return view('site.pages.interlaboratoriais', compact('interlabs'));
  }

  /**
   * Exibe a página de detalhes de um agendamento específico de interlab.
   *
   * @param AgendaInterlab $agendainterlab
   * @return View
   */
  public function exibePaginaAgendaInterlab(AgendaInterlab $agendainterlab) {

    $agendainterlab->load('interlab');

    return view('site.pages.single-interlaboratorial', compact('agendainterlab'));
  }

}
