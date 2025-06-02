<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Models\LancamentoFinanceiro;
use Maatwebsite\Excel\Concerns\FromView;

class LancamentosMesExport implements FromView
{
    public function __construct(public $mes, public $ano) {}

    public function view(): View
    {
        // Calcula datas de início e fim do mês
        $inicio = "{$this->ano}-" . str_pad($this->mes, 2, '0', STR_PAD_LEFT) . "-01";
        $fim = date('Y-m-d', strtotime("+1 month -1 day", strtotime($inicio)));

        $lancamentos = DB::table('lancamentos_financeiros')
            ->selectRaw("
                data_vencimento,
                nota_fiscal,
                pessoas.nome_razao,
                plano_contas.descricao as plano_conta,
                centro_custos.descricao as centro_custo,
                (CASE tipo_lancamento WHEN 'CREDITO' THEN valor END) AS credito,
                (CASE tipo_lancamento WHEN 'DEBITO' THEN valor END) AS debito,
                consiliacao
            ")
            ->join('pessoas', 'pessoa_id', '=', 'pessoas.id')
            ->join('plano_contas', 'plano_conta_id', '=', 'plano_contas.id')
            ->join('centro_custos', 'lancamentos_financeiros.centro_custo_id', '=', 'centro_custos.id')
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->orderBy('data_vencimento')
            ->get();

        return view('excel.lancamentos-financeiros-mes', [
            'lancamentos' => $lancamentos,
            'inicio' => $inicio,
            'fim' => $fim
        ]);
    }
}