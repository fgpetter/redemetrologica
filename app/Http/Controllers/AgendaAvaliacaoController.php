<?php

namespace App\Http\Controllers;

use App\Models\Avaliador;
use App\Models\Laboratorio;
use App\Models\TipoAvaliacao;
use App\Models\AgendaAvaliacao;
use App\Models\AreaAvaliada;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class AgendaAvaliacaoController extends Controller
{
    /**
     * Gera tela de lista de avaliacoes agendados
     * 
     * @return View
     */
    public function index(): View
    {
        return view('painel.avaliacoes.index');
    }

    /**
     * Adiciona uma avaliação
     *
     * @param Request $request
     * @return RedirectResponse
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
                'laboratorio_uid.exists' => 'Dados inválidos, selecione um laboratório e envie novamente'
            ]
        );

        $laboratorio = Laboratorio::select('id')->where('uid', $request->laboratorio_uid)->first();

        // cria uma avaliação vinculada a um laboratorio
        $avaliacao = AgendaAvaliacao::create([
            'laboratorio_id' => $laboratorio->id,
        ]);

        if (!$avaliacao) {
            return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        return redirect()->route('avaliacao-insert', $avaliacao->uid)
            ->with('success', 'Avaliação criada com sucesso');
    }

    /**
     * Tela de edição de avaliacao
     *
     * @param AgendaAvaliacao $avaliacao
     * @return View
     **/
    public function insert(AgendaAvaliacao $avaliacao): View
    {
        $laboratorio = Laboratorio::find($avaliacao->laboratorio_id);
        $avaliadores = Avaliador::with('pessoa:id,uid,nome_razao')->get();
        $tipo_avaliacao = TipoAvaliacao::select('id', 'descricao')->get();


        return view('painel.avaliacoes.insert', 
            [
                'avaliacao' => $avaliacao,
                'laboratorio' => $laboratorio,
                'avaliadores' => $avaliadores,
                'tipo_avaliacao' => $tipo_avaliacao,
            ]);
    }

    /**
     * Atualiza dados da avaliação
     *
     * @param Request $request
     * @param AgendaAvaliacao $avaliacao
     * @return RedirectResponse
     */
    public function update(Request $request, AgendaAvaliacao $avaliacao): RedirectResponse
    {
        $validate = $request->validate([
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date'],
            'tipo_avaliacao_id' => ['nullable', 'numeric', 'exists:tipo_avaliacoes,id'],
            'laboratorio_interno_id' => ['nullable', 'numeric', 'exists:laboratorios_internos,id'],
            'status_proposta' => ['nullable', 'string', Rule::in(['PENDENTE', 'AGUARDANDO', 'APROVADA', 'REPROVADA'])], 
            'fr_28' => ['nullable', 'numeric', 'in:0,1'],
            'fr_41' => ['nullable', 'numeric', 'in:0,1'],
            'fr_101' => ['nullable', 'numeric', 'in:0,1'],
            'fr_48' => ['nullable', 'numeric', 'in:0,1'],
            'relatorio_fr06' => ['nullable', 'string', Rule::in(['INCOMPLETA','COMPLETA','ENVIADA TODOS','ENVIADA AVALIADORES','ENVIADA LABORATORIO','NAO ENVIADA COMPLETA','APROVADA TODOS','APROVADA AVALIADORES','NAO APROVADA'])],
            'data_proc_laboratorio' => ['nullable', 'date'],
            'proc_laboratorio' => ['nullable', 'numeric', 'in:0,1'],
            'inf_avaliadores' => ['nullable', 'numeric', 'in:0,1'],
            'carta_reconhecimento' => ['nullable', 'numeric', 'in:0,1'],
            'retorno_fr06' => ['nullable', 'date'],
            'pesq_satisfacao' => ['nullable', 'date'],
            'data_proposta_acoes_corretivas' => ['nullable', 'date'],
            'data_acoes_corretivas' => ['nullable', 'date'],
            'acoes_aceitas' => ['nullable', 'string', 'in:NAO,SIM,PARCIALMENTE'],
            'data_reuniao_comite' => ['nullable', 'date'],
            'comite' => ['nullable', 'string', 'in:APROVADO,NAO APROVADO,COM PENDENCIAS'],
            'prazo_ajuste_pos_comite' => ['nullable', 'date'],
            'certificado' => ['nullable', 'numeric', 'in:0,1'],
            'validade_certificado' => ['nullable', 'date'],
            'enviado_certificado' => ['nullable', 'string', 'in:ENVIADO,NAO ENVIADO,PENDENTE'],
            'data_publicacao_site' => ['nullable', 'date'],
            'certificado_impresso' => ['nullable', 'numeric', 'in:0,1'],
            'ano_revisao_certificado' => ['nullable', 'date'],
            'obs' => ['nullable', 'string']
            ],
        [
            'data_inicio.date' => 'Data de inicio inválida',
            'data_fim.date' => 'Data de fim inválida',
            'tipo_avaliacao_id.numeric' => 'Selecione uma opção válida',
            'tipo_avaliacao_id.exists' => 'Selecione uma opção válida',
            'fr_28.in' => 'Selecione uma opção válida',
            'fr_41.in' => 'Selecione uma opção válida',
            'fr_101.in' => 'Selecione uma opção válida',
            'fr_48.in' => 'Selecione uma opção válida',
            'relatorio_fr06.in' => 'Selecione uma opção válida',
            'laboratorio_interno_id.numeric' => 'Selecione uma opção válida',
            'laboratorio_interno_id.exists' => 'Selecione uma opção válida',
            'data_proc_laboratorio.date' => 'Data de processo de laboratório inválida',
            'proc_laboratorio.in' => 'Selecione uma opção válida',
            'inf_avaliadores.in' => 'Selecione uma opção válida',
            'carta_reconhecimento.in' => 'Selecione uma opção válida',
            'retorno_fr06.date' => 'Data de retorno inválida',
            'pesq_satisfacao.date' => 'Data da pesquisa inválida',
            'data_proposta_acoes_corretivas.date' => 'Data da proposta de ações corretivas inválida',
            'data_acoes_corretivas.date' => 'Data das ações corretivas inválida',
            'acoes_aceitas.in' => 'Selecione uma opção válida',
            'data_reuniao_comite.date' => 'Data da reunião do comite inválida',
            'comite.in' => 'Selecione uma opção válida',
            'prazo_ajuste_pos_comite.date' => 'Data do ajuste do prazo inválida',
            'data_publicacao_site.date' => 'Data da publicação no site inválida',
            'certificado.in' => 'Selecione uma opção válida',
            'validade_certificado.date' => 'Data de validade do certificado inválida',
            'enviado_certificado.in' => 'Selecione uma opção válida',
            'certificado_impresso.in' => 'Selecione uma opção válida',
            'ano_revisao_certificado.date' => 'Data da revisão do certificado inválida',
            'obs.string' => 'Conteúdo inválido'

        ]);

        $valor_proposta = formataMoeda( $request->valor_proposta);
        $validate['valor_proposta'] = $valor_proposta;
        
        $validate['data_proc_laboratorio'] = $request->data_proc_laboratorio ?? Carbon::parse($request->data_inicio)->addDays(-10)->format('Y-m-d');
        $validate['data_proposta_acoes_corretivas'] = $request->data_proposta_acoes_corretivas ?? Carbon::parse($request->data_fim)->addDays(7)->format('Y-m-d');
        $validate['data_acoes_corretivas'] = $request->data_acoes_corretivas ?? Carbon::parse($request->data_fim)->addDays(45)->format('Y-m-d');
        $validate['validade_certificado'] = $request->validade_certificado ?? Carbon::parse($request->data_fim)->addYears(1)->addMonths(3)->format('Y-m-d');

        $avaliacao->update($validate);

        return redirect()->back()->with('success', 'Dados atualizados com sucesso');
    }

    /**
     * Remove agendamento de avaliacao
     * 
     * @param AgendaAvaliacao $avaliacao
     * @return RedirectResponse
     */
    public function delete(AgendaAvaliacao $avaliacao): RedirectResponse
    {
        $avaliacao->delete();

        return redirect()->back()->with('warning', 'Avaliação removida com sucesso');
    }

    /**
     * Salva uma nova area avaliada
     * @param AreaAvaliada $area
     * @param Request $request
     * @return RedirectResponse
     */
    public function saveArea(AreaAvaliada $area, Request $request): RedirectResponse
    {

        $validate = $request->validate([
            'avaliacao_id' => ['required', 'exists:agenda_avaliacoes,id'],
            'area_atuacao_id' => ['required', 'exists:areas_atuacao,id'],
            'situacao' => ['nullable', 'string', Rule::in(['ATIVO','AVALIADOR','AVALIADOR EM TREINAMENTO','AVALIADOR LIDER','ESPECIALISTA','INATIVO'])],
            'data_inicial' => ['nullable', 'date'],
            'data_final' => ['nullable', 'date'],
            'avaliador_id' => ['required', 'exists:avaliadores,id'],
            'num_ensaios' => ['nullable', 'integer'],
            'dias' => ['nullable', 'integer'],
        ],[
            'avaliacao_id.required' => 'Dados inválidos, selecione uma avaliação e envie novamente',
            'avaliacao_id.exists' => 'Dados inválidos, selecione uma avaliação e envie novamente',
            'area_atuacao_id.required' => 'Dados inválidos, selecione uma area e envie novamente',
            'area_atuacao_id.exists' => 'Dados inválidos, selecione uma area e envie novamente',
            'situacao.in' => 'Selecione uma opção válida',
            'data_inicial.date' => 'Data inicial inválida',
            'data_final.date' => 'Data final inválida',
            'avaliador_id.required' => 'Selecione um avaliador e envie novamente',
            'avaliador_id.exists' => 'Selecione um avaliador e envie novamente',
            'num_ensaios.integer' => 'O dado enviado não é valido',
        ]);

        $validate['valor_dia']  = formataMoeda($request->valor_dia );
        $validate['valor_estim_desloc']  = formataMoeda($request->valor_estim_desloc );
        $validate['valor_estim_alim']  = formataMoeda($request->valor_estim_alim );
        $validate['valor_estim_hosped']  = formataMoeda($request->valor_estim_hosped );
        $validate['valor_estim_extras'] = formataMoeda($request->valor_estim_extras );
        $validate['valor_lider'] = formataMoeda($request->valor_lider );
        $validate['valor_real_desloc'] = formataMoeda($request->valor_real_desloc );
        $validate['valor_real_alim'] = formataMoeda($request->valor_real_alim );
        $validate['valor_real_hosped'] = formataMoeda($request->valor_real_hosped );
        $validate['valor_real_extras'] = formataMoeda($request->valor_real_extras );


        $validate['valor_avaliador'] = ($validate['dias'] * $validate['valor_dia']) + ($validate['valor_lider']); // verificar regra correta
        
        $validate['total_gastos_estim'] = $validate['valor_estim_desloc'] + $validate['valor_estim_alim'] + $validate['valor_estim_hosped'] + $validate['valor_estim_extras'] + $validate['valor_avaliador'];
        // como estava
        // $validate['total_gastos_reais'] = $validate['valor_lider'] + $validate['valor_avaliador'] + $validate['valor_real_desloc'] + $validate['valor_real_alim'] + $validate['valor_real_hosped'] + $validate['valor_real_extras']; //como estava
       
        $validate['total_gastos_reais'] = $validate['valor_real_desloc'] + $validate['valor_real_alim'] + $validate['valor_real_hosped'] + $validate['valor_real_extras'] + $validate['valor_avaliador'];

        if($area->uid){

            $area->update($validate);
        } else {

            AreaAvaliada::create($validate);
        }

        return redirect()->back()->with('success', 'Dados atualizados com sucesso');
    }

    public function deleteArea(AreaAvaliada $area): RedirectResponse
    {
        $area->delete();

        return redirect()->back()->with('warning', 'Area removida com sucesso');
    }

}
