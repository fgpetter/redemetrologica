<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use App\Models\AgendaInterlab;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
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
    ]);

    $lancamentosfinanceiros = LancamentoFinanceiro::getLancamentosFinanceiros($validated)
      ->orderBy('data_emissao', 'desc')
      ->get();

    $pessoas = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')
      ->whereIn('id', LancamentoFinanceiro::select('pessoa_id'))
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
    $validated = $request->validate(
      [
        'data_emissao' => ['nullable'],
        'consiliacao' => ['nullable'],
        'documento' => ['nullable'],
        'nota_fiscal' => ['nullable'],
        'pessoa_id' => ['required', 'exists:pessoas,id'],
        'centro_custo_id' => ['required', 'exists:centro_custos,id'],
        'historico' => ['nullable'],
        'banco_id' => ['nullable'],
        'tipo_lancamento' => ['required', 'in:CREDITO,DEBITO'],
        'valor' => ['nullable'],
        'data_vencimento' => ['nullable'],
        'data_pagamento' => ['nullable'],
        'status' => ['required', 'in:EFETIVADO,PROVISIONADO'],
        'observacoes' => ['nullable'],
      ],
      [
        'status.required' => 'O campo Status é obrigatório',
        'status.in' => 'A opção selecionada é inválida',
        'tippo_lancamento.required' => 'O campo Tipo de Lancamento é obrigatório',
        'tipo_lancamento.in' => 'A opção selecionada é inválida',
      ]
    );
    $validated['valor'] = formataMoeda($validated['valor']) ?? null;
    $validated['uid'] = config('hashing.uid');


    $lancamento_financeiro = LancamentoFinanceiro::create($validated);

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
    $pessoasT = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')->whereNot('id', $lancamento->pessoa_id)->get();
    $pessoaLancamento = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')
      ->where('id', $lancamento->pessoa_id)
      ->withTrashed()
      ->first();
    if ($pessoaLancamento) {
      $pessoas = $pessoasT->push($pessoaLancamento);
    } else {
      $pessoas = $pessoasT;
    }

    $centrosdecustoT = CentroCusto::whereNot('id', $lancamento->centro_custo_id)->orderBy('descricao')->get();
    $centrocusto_lancamento = CentroCusto::where('id', $lancamento->centro_custo_id)
      ->withTrashed()
      ->first();
    if ($centrocusto_lancamento) {
      $centrosdecusto = $centrosdecustoT->push($centrocusto_lancamento);
    } else {
      $centrosdecusto = $centrosdecustoT;
    }

    $planoConta = PlanoConta::all();
    $modalidadePagamento = ModalidadePagamento::all();

    return view('painel.lancamento-financeiro.insert', [
      'lancamento' => $lancamento,
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

    $validated = $request->validate(
      [
        'data_emissao' => ['nullable'],
        'consiliacao' => ['nullable'],
        'documento' => ['nullable'],
        'nota_fiscal' => ['nullable'],
        'pessoa_id' => ['required', 'exists:pessoas,id'],
        'centro_custo_id' => ['required', 'exists:centro_custos,id'],
        'historico' => ['nullable'],
        'banco_id' => ['nullable'],
        'tipo_lancamento' => ['required', 'in:CREDITO,DEBITO'],
        'valor' => ['nullable'],
        'data_vencimento' => ['nullable'],
        'data_pagamento' => ['nullable'],
        'status' => ['required', 'in:EFETIVADO,PROVISIONADO'],
        'observacoes' => ['nullable'],
      ],
      [
        'status.required' => 'O campo Status é obrigatório',
        'status.in' => 'A opção selecionada é inválida',
        'tippo_lancamento.required' => 'O campo Tipo de Lancamento é obrigatório',
        'tipo_lancamento.in' => 'A opção selecionada é inválida',
      ]
    );

    $validated['valor'] = formataMoeda($validated['valor']) ?? null;
    $lancamento->update($validated);

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

    if( Arr::exists( $validated, 'curso' ) || Arr::exists( $validated, 'pep' )) {
      $validated['area'] = match( $validated['area'] ?? null ) {
        'CURSO' => 'agenda_curso_id',
        'PEP' => 'agenda_interlab_id',
        'AVALIACAO' => 'agenda_avaliacao_id',
        default => null,
      };
    }

    $lancamentosfinanceiros = LancamentoFinanceiro::getLancamentosAReceber($validated)
      ->orderBy('data_emissao', 'desc')
      ->paginate(15);

    $pessoas = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')
      ->whereIn('id', LancamentoFinanceiro::select('pessoa_id'))
      ->withTrashed()
      ->get();

    $cursos = AgendaCursos::select('id','uid','curso_id')
      ->whereIn('status', ['CONFIRMADO','REALIZADO'])
      ->with('curso')
      ->get();

    $agendainterlabs = AgendaInterlab::select('id','uid','interlab_id')
      ->whereIn('status', ['CONFIRMADO','CONCLUIDO'])
      ->with('interlab')
      ->get();

    return view('painel.lancamento-financeiro.areceber', [
      'lancamentosfinanceiros' => $lancamentosfinanceiros, 
      'pessoas' => $pessoas, 
      'cursos' => $cursos,
      'agendainterlabs' => $agendainterlabs
    ]);
  }
}
