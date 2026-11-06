<?php

namespace App\Http\Controllers;

use App\Actions\Financeiro\EditarLancamentosEmLoteAction;
use App\Exports\LancamentosMesExport;
use App\Http\Requests\AtualizarLancamentoFinanceiroRequest;
use App\Http\Requests\AtualizarLancamentosEmLoteRequest;
use App\Http\Requests\CriarLancamentoFinanceiroRequest;
use App\Http\Requests\FiltrarLancamentosAReceberRequest;
use App\Models\AgendaCursos;
use App\Models\AgendaInterlab;
use App\Models\CentroCusto;
use App\Models\LancamentoFinanceiro;
use App\Models\ModalidadePagamento;
use App\Models\Pessoa;
use App\Models\PlanoConta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LancamentoFinanceiroController extends Controller
{
    /**
     * Gera pagina de listagem lancamentos financeiros
     *
     **/
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'data_inicial' => ['nullable', Rule::date()->format('Y-m-d')],
            'data_final' => ['nullable', Rule::date()->format('Y-m-d')],
            'pessoa' => ['nullable', 'exists:pessoas,id'],
            'tipo_data' => ['nullable', 'in:data_vencimento,data_pagamento'],
        ]);

        if (empty($validated['data_inicial'])) {
            $validated['data_inicial'] = today()->format('Y-m-d');
        }

        if (empty($validated['data_final'])) {
            $validated['data_final'] = today()->addDays(7)->format('Y-m-d');
        }

        $lancamentosfinanceiros = LancamentoFinanceiro::getLancamentosFinanceiros($validated)
            ->orderBy('data_vencimento')
            ->get();

        $pessoas = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')
            ->whereHas('lancamentosfinanceiros', function ($query) {
                $query->where('status', 'EFETIVADO')->orWhere('tipo_lancamento', 'DEBITO');
            })
            ->withTrashed()
            ->get();

        $meses_anos = LancamentoFinanceiro::selectRaw("DATE_FORMAT(data_pagamento, '%m-%Y') as mes_ano")
            ->whereNotNull('data_pagamento')
            ->distinct()
            ->orderByRaw('MIN(data_pagamento) DESC') // para retornar em ordem cronológica
            ->groupBy('mes_ano')
            ->pluck('mes_ano');

        $centrosdecusto = CentroCusto::all()->sortBy('descricao');
        $planosconta = PlanoConta::all();
        $modalidadepagamento = ModalidadePagamento::all();
        $pessoasModal = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')
            ->orderBy('nome_razao')
            ->get();

        return view('painel.lancamento-financeiro.index', [
            'lancamentosfinanceiros' => $lancamentosfinanceiros,
            'pessoas' => $pessoas,
            'mesesanos' => $meses_anos,
            'centrosdecusto' => $centrosdecusto,
            'planosconta' => $planosconta,
            'modalidadepagamento' => $modalidadepagamento,
            'pessoasModal' => $pessoasModal,
        ]);
    }

    /**
     * Adiciona lancamento financeiro
     *
     **/
    public function create(CriarLancamentoFinanceiroRequest $request): RedirectResponse
    {

        $validated = $request->validated();

        $prepared_data = array_merge($validated, [
            'valor' => formataMoeda($request->valor),
            'status' => $request->data_pagamento ? 'EFETIVADO' : 'PROVISIONADO',
        ]);

        unset($prepared_data['duplicar_lancamento'], $prepared_data['lancamento_original_uid']);

        if ($request->boolean('duplicar_lancamento')) {
            $prepared_data['observacoes'] = null;
        }

        $lancamento_financeiro = LancamentoFinanceiro::create($prepared_data);

        if (! $lancamento_financeiro) {
            return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        return redirect()->route('lancamento-financeiro-index')->with('success', 'Lancamento cadastrado com sucesso');
    }

    /**
     * Tela de edição de lancamento
     *
     **/
    public function insert(LancamentoFinanceiro $lancamento): View
    {
        if ($lancamento->exists) {
            $lancamento->load(['pessoa' => fn ($q) => $q->with(['enderecoCobranca', 'enderecos'])]);
        }
        $pessoas = Pessoa::select('id', 'nome_razao', 'cpf_cnpj')->whereNot('id', $lancamento?->pessoa_id)->get();

        $centrosdecusto = CentroCusto::all()->sortBy('descricao');
        $planoConta = PlanoConta::all();
        $modalidadePagamento = ModalidadePagamento::all();

        return view('painel.lancamento-financeiro.edit', [
            'lancamento' => $lancamento ?? null,
            'pessoas' => $pessoas,
            'centrosdecusto' => $centrosdecusto,
            'planosconta' => $planoConta,
            'modalidadepagamento' => $modalidadePagamento,
        ]);
    }

    /**
     * Atualiza dados de lancamento
     *
     **/
    public function update(AtualizarLancamentoFinanceiroRequest $request, LancamentoFinanceiro $lancamento): RedirectResponse
    {

        $prepared_data = array_merge($request->validated(), [
            'valor' => formataMoeda($request->valor),
            'status' => $request->data_pagamento ? 'EFETIVADO' : 'PROVISIONADO',
        ]);

        $lancamento->update($prepared_data);

        return redirect()->back()->with('success', 'Lancamento atualizado com sucesso');
    }

    /**
     * Remove lancamento
     *
     **/
    public function delete(LancamentoFinanceiro $lancamento): RedirectResponse
    {
        $lancamento->delete();

        return redirect()->route('lancamento-financeiro-index')->with('warning', 'Lancamento removido');
    }

    /**
     * Atualiza em lote consiliacao, nota_fiscal, data_pagamento e data_vencimento (lote homogêneo: créditos ou débitos).
     */
    public function batchUpdate(AtualizarLancamentosEmLoteRequest $request, EditarLancamentosEmLoteAction $editarLancamentosEmLoteAction): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $atualizados = $editarLancamentosEmLoteAction->execute(
                $validated['uids'],
                [
                    'consiliacao' => $validated['consiliacao'] ?? null,
                    'nota_fiscal' => $validated['nota_fiscal'] ?? null,
                    'data_pagamento' => $validated['data_pagamento'] ?? null,
                    'data_vencimento' => $validated['data_vencimento'] ?? null,
                ]
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', 'Não foi possível atualizar os lançamentos.');
        }

        if ($atualizados === 0) {
            return redirect()->back()->with('success', 'Nenhum dado para atualizar.');
        }

        return redirect()->back()->with(
            'success',
            $atualizados === 1
              ? '1 lançamento atualizado com sucesso.'
              : "{$atualizados} lançamentos atualizados com sucesso."
        );
    }

    /**
     * Gera pagina de listagem lancamentos a receber
     *
     **/
    public function areceber(FiltrarLancamentosAReceberRequest $request): View
    {
        $validated = $request->validated();

        if ($validated['area'] ?? false) {
            $validated['area'] = match ($validated['area']) {
                'CURSO' => 'agenda_curso_id',
                'PEP' => 'agenda_interlab_id',
                'AVALIACAO' => 'agenda_avaliacao_id',
                default => null,
            };
        }

        if (($validated['curso'] ?? false) || ($validated['pep'] ?? false)) {
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

        $agendainterlabs = AgendaInterlab::select('agenda_interlabs.id', 'agenda_interlabs.uid', 'agenda_interlabs.interlab_id', 'agenda_interlabs.ano_referencia', 'interlabs.nome')
            ->join('interlabs', 'agenda_interlabs.interlab_id', '=', 'interlabs.id')
            ->whereNot('agenda_interlabs.status', 'CANCELADO')
            ->orderBy('interlabs.nome')
            ->get();

        return view('painel.lancamento-financeiro.areceber', [
            'lancamentosfinanceiros' => $lancamentosfinanceiros,
            'pessoas' => $pessoas,
            'cursos' => $cursos,
            'agendainterlabs' => $agendainterlabs,
        ]);
    }

    /**
     * Exporta os lançamentos financeiros do mês/ano para XLSX
     *
     * @param  int  $mes
     * @param  int  $ano
     */
    public function exportLancamentosMes(Request $request): BinaryFileResponse
    {
        $mes_ano = explode('-', $request->mesano);
        $mes = $mes_ano[0];
        $ano = $mes_ano[1];
        $nomeArquivo = "lancamentos-financeiros-{$mes}-{$ano}.xlsx";

        return Excel::download(new LancamentosMesExport($mes, $ano), $nomeArquivo);
    }
}
