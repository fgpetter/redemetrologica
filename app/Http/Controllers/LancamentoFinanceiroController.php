<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\CentroCusto;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\LancamentoFinanceiro;
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
    $lancamentosfinanceiros = LancamentoFinanceiro::select('uid','pessoa_id', 'data_vencimento', 'valor', 'data_pagamento', 'historico')
      ->with('pessoa')
      ->orderBy('data_vencimento', 'desc')
      ->paginate(15);

    return view('painel.lancamento-financeiro.index', ['lancamentosfinanceiros' => $lancamentosfinanceiros]);
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
        'num_documento' => ['nullable'],
        'documento' => ['nullable'],
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
    $pessoas = Pessoa::select('id', 'nome_razao')->get();
    $centrosdecusto = CentroCusto::select('id', 'descricao')->get();
    return view('painel.lancamento-financeiro.insert', [
        'lancamento' => $lancamento, 
        'pessoas' => $pessoas, 
        'centrosdecusto' => $centrosdecusto]);
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
        'data_autorizacao' => ['nullable'],
        'enviado_banco' => ['nullable', 'integer', 'in:0,1'],
        'num_cheque' => ['nullable'],
        'documento' => ['nullable'],
        'pessoa_id' => ['nullable'],
        'centro_custo_id' => ['nullable'],
        'historico' => ['nullable'],
        'banco_id' => ['nullable'],
        'tipo_lancamento' => ['required', 'in:CREDITO,DEBITO'],
        'valor_bruto' => ['nullable'],
        'desconto' => ['nullable'],
        'valor' => ['nullable'],
        'data_vencimento' => ['nullable'],
        'competencia_folha' => ['nullable'],
        'modalidade_pagamento_id' => ['nullable'],
        'data_pagamento' => ['nullable'],
        'status' => ['required', 'in:EFETIVADO,PROVISIONADO'],
        'conciliado' => ['nullable', 'integer', 'in:0,1'],
        'observacoes' => ['nullable'],
    ],
    [
        'data_autorizacao.in' => 'A opção selecionada é inválida',
        'enviado_banco.in' => 'A opção selecionada é inválida',
        'status.in' => 'A opção selecionada é inválida',
        'conciliado.in' => 'A opção selecionada é inválida',                
    ]);

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
    return redirect()->route('lancamento-financeiro-index')->with('success', 'Lancamento removido');
  }

  /**
   * Undocumented function
   *
   * @param string $valor
   * @return string|null
   */
  private function formataMoeda($valor): ?string
  {
    if($valor){
      return str_replace(',','.', str_replace('.','', $valor));
    } else {
      return null;
    }
  }
}
