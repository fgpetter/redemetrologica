<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\Interlab;
use App\Models\Parametro;
use App\Exports\LabExport;
use Illuminate\Http\Request;
use App\Models\AgendaInterlab;
use App\Models\MaterialPadrao;
use Illuminate\Support\Carbon;
use App\Models\InterlabDespesa;
use App\Models\InterlabInscrito;
use App\Actions\FileUploadAction;
use App\Models\InterlabParametro;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\RedirectResponse;
use App\Models\AgendainterlabMaterial;
use App\Models\InterlabRodadaParametro;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreAgendaInterlabRequest;
use App\Jobs\EnviarConfirmacaoInterlabJob;

class AgendaInterlabController extends Controller
{
   /**
   * Gera tela de lista de interlabs agendados
   * 
   * @return View
   */
  public function index(): View
  {
    return view('painel.agenda-interlab.index');
  }

   /**
   * Tela de cadastro e ediçao de agenda de interlabs
   * 
   * @param AgendaInterlab $agendainterlab
   * @return View
   */
  public function insert(AgendaInterlab $agendainterlab): View
  {
    $agendainterlab->load(['despesas', 'parametros', 'rodadas', 'valores']);
    $intelabinscritos = InterlabInscrito::where('agenda_interlab_id', $agendainterlab->id)
      ->with(['empresa', 'pessoa', 'laboratorio']);

      $data = [
      'pessoas' => Pessoa::select(['id', 'uid', 'cpf_cnpj', 'nome_razao', 'tipo_pessoa'])->orderBy('nome_razao')->get(),
      'agendainterlab' => $agendainterlab,
      'interlabs' => Interlab::all(),
      'materiaisPadrao' => MaterialPadrao::whereIn('tipo', ['INTERLAB', 'AMBOS'])->orderBy('descricao')->get(),
      'interlabDespesa' => $agendainterlab->despesas,
      'fabricantes' => DB::table('interlab_despesas')->distinct()->get(['fabricante']),
      'fornecedores' => DB::table('interlab_despesas')->distinct()->get(['fornecedor']),
      'interlabParametros' => $agendainterlab->parametros,
      'parametros' => Parametro::orderBy('descricao')->get(),
      'intelabinscritos' => $intelabinscritos->get(),
      'interlabempresasinscritas' => $intelabinscritos->distinct()->get(['empresa_id']),
      'idinterlab' => $agendainterlab->id, // id da agenda para uso no componente Livewire ListParticipantes
    ];

    return view('painel.agenda-interlab.insert', $data);
  }

  /**
   * Adiciona agenda interlab na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(StoreAgendaInterlabRequest $request): RedirectResponse
  {

    $validated = $request->validated();

    $valores_data = $validated['valores'] ?? null;
    unset($validated['valores']);

    $prepared_data = array_merge($validated, [
      'valor_desconto' => formataMoeda($request->valor_desconto),
      'descricao' => $request->descricao ? $this->salvaImagensTemporarias($request->descricao) : null,
    ]);

    try {
      DB::transaction(function () use ($prepared_data, $valores_data, &$agenda_interlab) {

        $agenda_interlab = AgendaInterlab::create($prepared_data);

        if (!empty($valores_data) && is_array($valores_data)) {
          foreach ($valores_data as $valor_data) {

            if( is_null($valor_data['descricao']) 
              && is_null($valor_data['valor']) 
              && is_null($valor_data['valor_assoc']) 
              ){
              continue;
            }

            $agenda_interlab->valores()->create([
              'descricao' => $valor_data['descricao'],
              'valor' => formataMoeda($valor_data['valor']),
              'valor_assoc' => formataMoeda($valor_data['valor_assoc']),
            ]);
          }
        }
      });
    } catch (\Throwable $e) {
      Log::error("Falha ao criar agenda interlab", [
        'user' => auth()->user() ?? null,
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'request' => $request->all(),
      ]);

      return redirect()->back()
        ->withInput()
        ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    if (empty($agenda_interlab) || !$agenda_interlab->id) {
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
  public function update(StoreAgendaInterlabRequest $request, AgendaInterlab $agendainterlab): RedirectResponse
  {
    $oldStatus = $agendainterlab->status;

    $validated = $request->validated();

    $valores_data = $validated['valores'] ?? null;
    unset($validated['valores']);

    $prepared_data = array_merge($validated, [
      'valor_desconto' => formataMoeda($validated['valor_desconto']),
      'descricao' => $request->descricao ? $this->salvaImagensTemporarias($request->descricao) : null,
      'site' => ($request->status === 'CONCLUIDO') ? 0 : ($request->site ?? 0),
      'inscricao' => ($request->status === 'CONCLUIDO') ? 0 : ($request->inscricao ?? 0),
      'destaque' => ($request->status === 'CONCLUIDO') ? 0 : ($request->destaque ?? 0),
    ]);


    try {
      DB::transaction(function () use ($agendainterlab, $prepared_data, $valores_data) {

        $agendainterlab->update($prepared_data);
        $agendainterlab->valores()->delete();

        if (!empty($valores_data) && is_array($valores_data)) {
          foreach ($valores_data as $valor_data) {

            if( is_null($valor_data['descricao']) 
              && is_null($valor_data['valor']) 
              && is_null($valor_data['valor_assoc']) 
              ){
              continue;
            }

            $agendainterlab->valores()->create([
              'descricao' => $valor_data['descricao'],
              'valor' => formataMoeda($valor_data['valor']),
              'valor_assoc' => formataMoeda($valor_data['valor_assoc']),
            ]);
          }
        }
      });
    } catch (\Throwable $e) {

      Log::error("Falha ao atualizar agenda interlab", [
        'user' => auth()->user() ?? null,
        'agenda_interlab_id' => $agendainterlab->id ?? null,
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'request' => $request->all(),
      ]);

      return back()
        ->withInput()
        ->with('error', 'Ocorreu um erro ao atualizar. Tente novamente mais tarde.');
    }
    // Dispara os emails de confirmação
    if ($oldStatus == 'AGENDADO' && $agendainterlab->status === 'CONFIRMADO') {
      $inscritos = InterlabInscrito::where('agenda_interlab_id', $agendainterlab->id)
        ->with('laboratorio')
        ->get();

      foreach ($inscritos as $index => $inscrito) {
        EnviarConfirmacaoInterlabJob::dispatch($inscrito)
          ->delay(now()->addSeconds(($index + 1) * 10));
      }
    }

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

      Log::channel('validation')->info("Erro de validação", 
      [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) .'::'. __FUNCTION__ ,
          'errors' => $validator->errors() ?? null,
      ]);

      return back()
      ->withErrors($validator, 'despesas')
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente')
      ->withFragment('despesas');
    }

    $prepared_data = $validator->validate();
    InterlabDespesa::updateOrCreate([
        'id' => $prepared_data['despesa_id'],
      ],[
        'agenda_interlab_id' => $prepared_data['agenda_interlab_id'],
        'material_padrao_id' => $prepared_data['material_padrao'],
        'quantidade' => $prepared_data['quantidade'],
        'valor' => formataMoeda( $prepared_data['valor'] ),
        'total' => formataMoeda( $prepared_data['total'] ),
        'lote' => $prepared_data['lote'],
        'validade' => $prepared_data['validade'],
        'data_compra' => $prepared_data['data_compra'],
        'fornecedor' => $prepared_data['fornecedor'],
        'fabricante' => $prepared_data['fabricante'],
        'cod_fabricante' => $prepared_data['cod_fabricante'],
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
   * Adiciona parametros no agendamento de PEP
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function salvaParametro(Request $request): RedirectResponse 
  {
    
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

      Log::channel('validation')->info("Erro de validação", 
      [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) .'::'. __FUNCTION__ ,
          'errors' => $validator->errors() ?? null,
      ]);

      return back()
      ->withErrors($validator, 'despesas')
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }
    $prepared_data = $validator->validate();
    $parametro = InterlabParametro::firstOrCreate([
      'agenda_interlab_id' => $prepared_data['agenda_interlab_id'],
      'parametro_id' => $prepared_data['parametro_id'],
    ]);

    if( !$parametro ){
      return back()->with('error', 'Falha ao cadastrar parametro')->withFragment('despesas');
    }

    return back()->with('success', 'Parâmetro salvo com sucesso')->withFragment('despesas');
  }

  /**
   * Remove parametros
   *
   * @param InterlabParametro $parametro
   * @param Request $request
   * @return RedirectResponse
   */
  public function deleteParametro(InterlabParametro $parametro, Request $request): RedirectResponse 
  {

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
   * Lida com imagens temporárias do editor de imagens 
   * e retorna o conteúdo atualizado para pasta correta
   * 
   * @return string $content
   */
  private function salvaImagensTemporarias($descricao): string
  {

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
    ->orderBy('data_inicio')
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

  /**
   * Gera XLSX com dados dos laboratórios inscritos
   *
   * @param AgendaInterlab $agendainterlab
   * @return void
   */
  public function exportLaboratoriosToXLS(AgendaInterlab $agendainterlab)
  {
    $interlabName = $agendainterlab->interlab->nome ?? 'interlab';
    $interlabName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $interlabName); // Adicionar nome do PEP no nome do arquivo
    $filename = 'inscritos-interlab-ID' . $agendainterlab->id . '-' . $interlabName . '.xlsx';
    return Excel::download(new LabExport($agendainterlab), $filename);
  }

  /**
   * Adiciona materiais ao interlab
   *
   * @param Request $request
   * @param AgendaInterlab $agendainterlab
   * @return RedirectResponse
   */
  public function uploadMaterial(Request $request, AgendaInterlab $agendainterlab): RedirectResponse
  {
    $validator = Validator::make(
      $request->all(),
      [
        'descricao' => ['nullable', 'string', 'max:190'],
        'arquivo' => ['required', 'mimes:jpeg,png,jpg,pdf,doc,docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:5120'],
      ],
      [
        'descricao.string' => 'O campo aceita somente texto.',
        'arquivo.mimes' => 'Apenas arquivos JPG,PNG e PDF são permitidos.',
        'arquivo.max' => 'O arquivo é muito grande, diminua o arquivo usando www.ilovepdf.com/pt/comprimir_pdf ou www.tinyjpg.com.',
        'arquivo.required' => 'Selecione um arquivo para enviar.',
      ]
    );

    if ($validator->fails()) {
      Log::channel('validation')->info(
        "Erro de validação",
        [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) . '::' . __FUNCTION__,
          'errors' => $validator->errors() ?? null,
        ]
      );

      return back()
        ->with('error', 'Houve um erro ao processar os dados, tente novamente')
        ->withErrors($validator)
        ->withInput();
    }

    if ($request->hasFile('arquivo')) {
      $file_name = FileUploadAction::handle($request, 'arquivo', 'interlab-material');
    }

    $agendainterlab->materiais()->create([
      'arquivo' => $file_name,
      'descricao' => $request->descricao
    ]);

    return back()->with('success', 'Material adicionado com sucesso');
  }

  /**
   * Remove materiais do interlab
   *
   * @param AgendainterlabMaterial $material
   * @return RedirectResponse
   */
  public function deleteMaterial(AgendainterlabMaterial $material): RedirectResponse
  {
    if (File::exists(public_path('interlab-material/' . $material->arquivo))) {
      File::delete(public_path('interlab-material/' . $material->arquivo));
    }

    $material->delete();

    return redirect()->back()->with('success', 'Material removido');
  }

}
