<?php

namespace App\Http\Controllers;

use App\Actions\Avaliacoes\AtualizarAgendaAvaliacaoAction;
use App\Actions\Avaliacoes\CalcularOrcamentoAvaliacaoAction;
use App\Actions\Avaliacoes\ExcluirAreaAvaliadaAction;
use App\Actions\Avaliacoes\GerarPdfPesquisaSatisfacaoAction;
use App\Actions\Avaliacoes\SalvarAreaAvaliadaAction;
use App\Actions\Avaliacoes\SalvarPesquisaSatisfacaoPainelAction;
use App\Http\Requests\AtualizarAgendaAvaliacaoRequest;
use App\Http\Requests\SalvarAreaAvaliadaRequest;
use App\Http\Requests\UpdatePesquisaSatisfacaoPainelRequest;
use App\Models\AgendaAvaliacao;
use App\Models\AreaAvaliada;
use App\Models\Avaliador;
use App\Models\Laboratorio;
use App\Models\TipoAvaliacao;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AgendaAvaliacaoController extends Controller
{
    /**
     * Gera tela de lista de avaliacoes agendados
     */
    public function index(): View
    {
        return view('painel.avaliacoes.index');
    }

    /**
     * Adiciona uma avaliação
     *
     **/
    public function create(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'laboratorio_uid' => ['required', 'string', 'exists:laboratorios,uid'],
            ],
            [
                'laboratorio_uid.required' => 'Dados inválidos, selecione um laboratório e envie novamente',
                'laboratorio_uid.string' => 'Dados inválidos, selecione um laboratório e envie novamente',
                'laboratorio_uid.exists' => 'Dados inválidos, selecione um laboratório e envie novamente',
            ]
        );

        $laboratorio = Laboratorio::select('id')->where('uid', $request->laboratorio_uid)->first();

        // cria uma avaliação vinculada a um laboratorio
        $avaliacao = AgendaAvaliacao::create([
            'laboratorio_id' => $laboratorio->id,
        ]);

        if (! $avaliacao) {
            return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        return redirect()->route('avaliacao-insert', $avaliacao->uid)
            ->with('success', 'Avaliação criada com sucesso');
    }

    /**
     * Tela de edição de avaliacao
     *
     **/
    public function insert(AgendaAvaliacao $avaliacao): View
    {
        $laboratorio = Laboratorio::find($avaliacao->laboratorio_id);
        $avaliadores = Avaliador::with('pessoa:id,uid,nome_razao')->get();
        $tipo_avaliacao = TipoAvaliacao::select('id', 'descricao')->get();
        $orcamento = app(CalcularOrcamentoAvaliacaoAction::class)->execute($avaliacao);

        return view('painel.avaliacoes.edit',
            [
                'avaliacao' => $avaliacao,
                'laboratorio' => $laboratorio,
                'avaliadores' => $avaliadores,
                'tipo_avaliacao' => $tipo_avaliacao,
                'totalavaliadores' => $orcamento['total_avaliadores'],
                'totalgeralavaliadores' => $orcamento['total_geral_avaliadores'],
                'valorProposta' => $orcamento['valor_proposta'],
            ]);
    }

    /**
     * Atualiza dados da avaliação
     */
    public function update(
        AtualizarAgendaAvaliacaoRequest $request,
        AgendaAvaliacao $avaliacao,
        AtualizarAgendaAvaliacaoAction $atualizarAgenda,
    ): RedirectResponse {
        $resultado = $atualizarAgenda->execute($avaliacao, $request->validated());

        if ($resultado['bloqueio'] !== null) {
            return redirect()->back()->with('error', $resultado['bloqueio']);
        }

        if ($resultado['pesquisaSemEmail']) {
            return redirect()->back()->with('warning', 'Pesquisa gerada, mas o laboratório não tem e-mail cadastrado. Copie o link na aba Pesquisa de Avaliação.');
        }

        return redirect()->back()->with('success', 'Dados atualizados com sucesso');
    }

    /**
     * Remove agendamento de avaliacao
     */
    public function delete(AgendaAvaliacao $avaliacao): RedirectResponse
    {
        $avaliacao->delete();

        return redirect()->back()->with('warning', 'Avaliação removida com sucesso');
    }

    /**
     * Salva uma nova area avaliada
     */
    public function saveArea(
        SalvarAreaAvaliadaRequest $request,
        SalvarAreaAvaliadaAction $salvarAreaAvaliadaAction,
        ?AreaAvaliada $area = null,
    ): RedirectResponse {
        $salvarAreaAvaliadaAction->execute(
            $area?->exists ? $area : null,
            $request->validated(),
        );

        return redirect()->back()->with('success', 'Dados atualizados com sucesso')->withFragment('laboratorios');
    }

    public function deleteArea(
        AreaAvaliada $area,
        ExcluirAreaAvaliadaAction $excluirAreaAvaliadaAction,
    ): RedirectResponse {
        $excluirAreaAvaliadaAction->execute($area);

        return redirect()->back()->with('warning', 'Area removida com sucesso');
    }

    /**
     * Atualiza a pesquisa de satisfação pelo painel.
     */
    public function updatePesquisa(
        UpdatePesquisaSatisfacaoPainelRequest $request,
        AgendaAvaliacao $avaliacao,
    ): RedirectResponse {
        app(SalvarPesquisaSatisfacaoPainelAction::class)->execute($avaliacao, $request->validated());

        return redirect()->back()->with('success', 'Dados atualizados com sucesso')->withFragment('pesquisa');
    }

    /**
     * Relatório de média das pesquisas de satisfação.
     */
    public function media(): View
    {
        return view('painel.avaliacoes.media');
    }

    /**
     * PDF da pesquisa de satisfação da avaliação.
     */
    public function pdfPesquisa(
        AgendaAvaliacao $avaliacao,
        GerarPdfPesquisaSatisfacaoAction $gerarPdfPesquisaSatisfacaoAction,
    ): BinaryFileResponse {
        return $gerarPdfPesquisaSatisfacaoAction->execute($avaliacao);
    }
}
