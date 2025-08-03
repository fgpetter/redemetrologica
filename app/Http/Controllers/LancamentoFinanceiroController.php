<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use App\Models\AgendaInterlab;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\{Request,RedirectResponse};
use App\Models\{AgendaCursos,Pessoa,CentroCusto,LancamentoFinanceiro,ModalidadePagamento,PlanoConta};

class LancamentoFinanceiroController extends Controller
{
  /**
   * Gera pagina de listagem lancamentos financeiros
   *
   * @param Request $request
   * @return View
   **/
  public function index(Request $request): View
  {
    $validated = $request->validate([
      'data_inicial' => ['nullable', 'date'],
      'data_final' => ['nullable', 'date'],
      'pessoa' => ['nullable', 'exists:pessoas,id'],
      'tipo_data' => ['nullable', 'in:data_vencimento,data_pagamento'],
    ]);
    
    if( empty($validated['data_inicial']) ) {
      $validated['data_inicial'] = today();
    }
    
    if( empty($validated['data_final']) ) {
      $validated['data_final'] = today()->addDays(7);
    }

    if( $validated['pessoa'] ?? false ) {
      unset($validated['data_inicial']);
      unset($validated['data_final']);
      unset($validated['tipo_data']);
    }

    $lancamentosfinanceiros = LancamentoFinanceiro::getLancamentosFinanceiros($validated)
      ->orderBy('data_vencimento')
      ->get();

    $pessoas = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')
      ->whereIn('id', LancamentoFinanceiro::select('pessoa_id')->where('status', 'EFETIVADO'))
      ->withTrashed()
      ->get();

    return view( 'painel.lancamento-financeiro.index', [
      'lancamentosfinanceiros' => $lancamentosfinanceiros, 
      'pessoas' => $pessoas
    ]);
  }

  /**
   * Adiciona lancamento financeiro
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function store(Request $request): RedirectResponse
  {
    $validator = Validator::make($request->all(), [
        'data_emissao' => ['nullable', 'date'],
        'nota_fiscal' => ['nullable', 'string','max:191'],
        'consiliacao' => ['nullable', 'string' ,'max:191'],
        'documento' => ['nullable', 'string' ,'max:191'],
        'pessoa_id' => ['required', 'exists:pessoas,id'],
        'centro_custo_id' => ['required', 'exists:centro_custos,id'],
        'plano_conta_id' => ['required', 'exists:plano_contas,id'],
        'historico' => ['nullable', 'string' ,'max:900'],
        'tipo_lancamento' => ['required', 'in:CREDITO,DEBITO'],
        'valor' => ['nullable', 'string' ,'max:11'],
        'data_vencimento' => ['nullable', 'date'],
        'data_pagamento' => ['nullable', 'date'],
        'modalidade_pagamento_id' => ['nullable', 'exists:modalidade_pagamentos,id'],
        'observacoes' => ['nullable', 'string'],
      ],[
        'data_emissao.date' => 'A data de emissão deve ser uma data válida',
        'nota_fiscal.string' => 'O número da nota fiscal é inválido',
        'nota_fiscal.max' => 'Máximo de caracteres excedido',
        'consiliacao.string' => 'O número da consiliação é inválido', 
        'consiliacao.max' => 'Máximo de caracteres excedido', 
        'documento.string' => 'O número do documento é inválido',
        'documento.max' => 'Máximo de caracteres excedido',
        'pessoa_id.required' => 'É necessário selecionar uma pessoa',
        'pessoa_id.exists' => 'A pessoa selecionada não existe no sistema',
        'centro_custo_id.required' => 'É necessário selecionar um centro de custo',
        'centro_custo_id.exists' => 'O centro de custo selecionado não existe no sistema',
        'plano_conta_id.required' => 'É necessário selecionar um plano de conta',
        'plano_conta_id.exists' => 'O plano de conta selecionado não existe no sistema',
        'historico.string' => 'O histórico informado é inválido',
        'historico.max' => 'Máximo de caracteres excedido',
        'tipo_lancamento.required' => 'É necessário selecionar o tipo de lançamento',
        'tipo_lancamento.in' => 'O tipo de lançamento selecionado é inválido',
        'valor.numeric' => 'O valor deve ser um número válido',
        'data_vencimento.date' => 'A data de vencimento deve ser uma data válida',
        'data_pagamento.date' => 'A data de pagamento deve ser uma data válida',
        'modalidade_pagamento_id.exists' => 'A modalidade de pagamento selecionada não existe no sistema',
        'observacoes.string' => 'As observações informadas são inválidas',
        'observacoes.max' => 'Máximo de caracteres excedido'
      ]
    );

    if($validator->fails()) {
        
      Log::channel('validation')->info("Erro de validação", 
      [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) .'::'. __FUNCTION__ ,
          'errors' => $validator->errors() ?? null,
      ]);

      return back()->with('error', 'Houve um erro a processar os dados, tente novamente')->withErrors($validator)->withInput();
    }

    $prepared_data = array_merge($validator->validate(),[
      'valor' => formataMoeda($request->valor ),
      'status' => $request->data_pagamento ? 'EFETIVADO' : 'PROVISIONADO'
    ]);

    $lancamento_financeiro = LancamentoFinanceiro::create($prepared_data);

    if (!$lancamento_financeiro) {
      return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('lancamento-financeiro-index')->with('success', 'Lancamento cadastrado com sucesso');
  }

  /**
   * Tela de edição de lancamento
   *
   * @param LancamentoFinanceiro $lancamento
   * @return View
   **/
  public function insert(LancamentoFinanceiro $lancamento): View
  {
    if($lancamento->exists){
      $lancamento->load(['pessoa', 'pessoa.enderecos']);
      $enderecocobranca = ($lancamento->pessoa->end_padrao) ? $lancamento->pessoa->enderecos->find($lancamento->pessoa->end_padrao) : $lancamento->pessoa->enderecos->first();
    }
    $pessoas = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')->whereNot('id', $lancamento?->pessoa_id)->get();

    $centrosdecusto = CentroCusto::all();
    $planoConta = PlanoConta::all();
    $modalidadePagamento = ModalidadePagamento::all();

    return view('painel.lancamento-financeiro.insert', [
      'lancamento' => $lancamento ?? null,
      'enderecocobranca' => $enderecocobranca ?? null,
      'pessoas' => $pessoas,
      'centrosdecusto' => $centrosdecusto,
      'planosconta' => $planoConta,
      'modalidadepagamento' => $modalidadePagamento
    ]);
  }

  /**
   * Atualiza dados de lancamento
   *
   * @param Request $request
   * @param LancamentoFinanceiro $lancamento
   * @return RedirectResponse
   **/
  public function update(Request $request, LancamentoFinanceiro $lancamento): RedirectResponse
  {

    $validator = Validator::make($request->all(), 
      [
        'data_emissao' => ['nullable', 'date'],
        'nota_fiscal' => ['nullable', 'string','max:191'],
        'consiliacao' => ['nullable', 'string' ,'max:191'],
        'documento' => ['nullable', 'string' ,'max:191'],
        'pessoa_id' => ['required', 'exists:pessoas,id'],
        'centro_custo_id' => ['required', 'exists:centro_custos,id'],
        'plano_conta_id' => ['required', 'exists:plano_contas,id'],
        'historico' => ['nullable', 'string' ,'max:900'],
        'tipo_lancamento' => ['required', 'in:CREDITO,DEBITO'],
        'valor' => ['nullable', 'string'],
        'data_vencimento' => ['nullable', 'date'],
        'data_pagamento' => ['nullable', 'date'],
        'modalidade_pagamento_id' => ['nullable', 'exists:modalidade_pagamentos,id'],
        'observacoes' => ['nullable', 'string'],
      ],[
        'data_emissao.date' => 'A data de emissão deve ser uma data válida',
        'nota_fiscal.string' => 'O número da nota fiscal é inválido',
        'consiliacao.string' => 'O número da consiliação é inválido', 
        'documento.string' => 'O número do documento é inválido',
        'pessoa_id.required' => 'É necessário selecionar uma pessoa',
        'pessoa_id.exists' => 'A pessoa selecionada não existe no sistema',
        'centro_custo_id.required' => 'É necessário selecionar um centro de custo',
        'centro_custo_id.exists' => 'O centro de custo selecionado não existe no sistema',
        'plano_conta_id.required' => 'É necessário selecionar um plano de conta',
        'plano_conta_id.exists' => 'O plano de conta selecionado não existe no sistema',
        'historico.string' => 'O histórico informado é inválido',
        'tipo_lancamento.required' => 'É necessário selecionar o tipo de lançamento',
        'tipo_lancamento.in' => 'O tipo de lançamento selecionado é inválido',
        'valor.numeric' => 'O valor deve ser um número válido',
        'data_vencimento.date' => 'A data de vencimento deve ser uma data válida',
        'data_pagamento.date' => 'A data de pagamento deve ser uma data válida',
        'modalidade_pagamento_id.exists' => 'A modalidade de pagamento selecionada não existe no sistema',
        'observacoes.string' => 'As observações informadas são inválidas',
        'nota_fiscal.max' => 'Máximo de caracteres excedido',
        'consiliacao.max' => 'Máximo de caracteres excedido',
        'documento.max' => 'Máximo de caracteres excedido',
        'historico.max' => 'Máximo de caracteres excedido',
        'observacoes.max' => 'Máximo de caracteres excedido'
      ]
    );

    if($validator->fails()) {
        
      Log::channel('validation')->info("Erro de validação", 
      [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) .'::'. __FUNCTION__ ,
          'errors' => $validator->errors() ?? null,
      ]);

      return back()->with('error', 'Houve um erro a processar os dados, tente novamente')->withErrors($validator)->withInput();
    }

    $prepared_data = array_merge($validator->validate(),[
      'valor' => formataMoeda($request->valor ),
      'status' => $request->data_pagamento ? 'EFETIVADO' : 'PROVISIONADO'
    ]);

    $lancamento->update($prepared_data);

    return redirect()->back()->with('success', 'Lancamento atualizado com sucesso');
  }

  /**
   * Remove lancamento
   *
   * @param LancamentoFinanceiro $lancamento
   * @return RedirectResponse
   **/
  public function delete(LancamentoFinanceiro $lancamento): RedirectResponse
  {
    $lancamento->delete();
    return redirect()->route('lancamento-financeiro-index')->with('warning', 'Lancamento removido');
  }

  /**
   * Gera pagina de listagem lancamentos a receber
   *
   * @param Request $request
   * @return View
   **/
  public function areceber(Request $request): View
  {
    $validated = $request->validate([
        'data_inicial' => ['nullable', 'date'],
        'data_final' => ['nullable', 'date'],
        'pessoa' => ['nullable', 'exists:pessoas,id'],
        'area' => ['nullable', 'in:CURSO,PEP,AVALIACAO'],
        'curso' => ['nullable', 'exists:agenda_cursos,id', Rule::prohibitedIf(boolval($request->pep))],
        'pep' => ['nullable', 'exists:agenda_interlabs,id'],
      ],[
        'curso.prohibited' => 'Essa seleção é conflituosa',
    ]);

    if( $validated['area'] ?? false) {
      $validated['area'] = match( $validated['area'] ) {
        'CURSO' => 'agenda_curso_id',
        'PEP' => 'agenda_interlab_id',
        'AVALIACAO' => 'agenda_avaliacao_id',
        default => null,
      };
    }

    if( ($validated['curso'] ?? false) || ($validated['pep'] ?? false) ) {
      unset($validated['area']);
    }

    $lancamentosfinanceiros = LancamentoFinanceiro::getLancamentosAReceber($validated)
      ->orderBy('data_vencimento')->paginate(10);

    $pessoas = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')
      ->whereIn('id', LancamentoFinanceiro::select('pessoa_id')->whereNot('status', 'EFETIVADO'))
      ->withTrashed()
      ->orderBy('nome_razao')
      ->get();

    $cursos = AgendaCursos::select('agenda_cursos.id', 'agenda_cursos.uid', 'agenda_cursos.curso_id', 'agenda_cursos.data_inicio')
      ->join('cursos', 'agenda_cursos.curso_id', '=', 'cursos.id')
      ->whereIn('agenda_cursos.id', LancamentoFinanceiro::whereNull('data_pagamento')->select('agenda_curso_id'))
      ->whereNot('agenda_cursos.status', 'CANCELADO')
      ->orderBy('cursos.descricao')
      ->get();

    $agendainterlabs = AgendaInterlab::select('agenda_interlabs.id', 'agenda_interlabs.uid', 'agenda_interlabs.interlab_id')
      ->join('interlabs', 'agenda_interlabs.interlab_id', '=', 'interlabs.id')
      ->whereNot('agenda_interlabs.status', 'CANCELADO')
      ->orderBy('interlabs.nome')
      ->get();

    return view('painel.lancamento-financeiro.areceber', [
      'lancamentosfinanceiros' => $lancamentosfinanceiros, 
      'pessoas' => $pessoas, 
      'cursos' => $cursos,
      'agendainterlabs' => $agendainterlabs
    ]);
  }
}
