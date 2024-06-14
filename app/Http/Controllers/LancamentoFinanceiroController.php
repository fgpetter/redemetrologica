<?php

namespace App\Http\Controllers;

use App\Models\AgendaCursos;
use App\Models\Pessoa;
use App\Models\CentroCusto;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\LancamentoFinanceiro;
use App\Models\ModalidadePagamento;
use App\Models\PlanoConta;
use Illuminate\Http\RedirectResponse;


class LancamentoFinanceiroController extends Controller
{
  /**
   * Gera pagina de listagem lancamentos financeiros
   *
   * @return View
   **/
  public function index(): View
  {
    $lancamentosfinanceiros = LancamentoFinanceiro::select()
      ->with(['pessoa' => function ($query) {
        $query->withTrashed();
      }])
      ->whereIn('status', ['EFETIVADO','PROVISIONADO'])
      ->orderBy('data_vencimento', 'desc')
      ->get();

    $pessoas = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')->get();

    return view(
      'painel.lancamento-financeiro.index', 
      ['lancamentosfinanceiros' => $lancamentosfinanceiros, 'pessoas' => $pessoas]);
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
    $validated['valor'] = $this->formataMoeda($validated['valor']) ?? null;
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

    $validated['valor'] = $this->formataMoeda($validated['valor']) ?? null;
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
   * @return View
   **/
  public function areceber(): View
  {
    $lancamentosfinanceiros = LancamentoFinanceiro::select()
    ->with(['pessoa' => function ($query) {
      $query->withTrashed();
    }])
    ->where('status', 'A RECEBER')
    ->orderBy('data_emissao', 'desc')
    ->paginate(15);

    $pessoas = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')->get();

    $cursos = AgendaCursos::select('id','uid','curso_id')->whereIn('status', ['AGENDADO', 'CONFIRMADO'])->with('curso')->get();


    return view('painel.lancamento-financeiro.areceber', 
    ['lancamentosfinanceiros' => $lancamentosfinanceiros, 'pessoas' => $pessoas, 'cursos' => $cursos]);
  }




  /**
   * Undocumented function
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
