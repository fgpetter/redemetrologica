<?php

namespace App\Http\Controllers;

use App\Actions\Financeiro\AtualizarLancamentoCursoAction;
use App\Actions\SalvaInscritoAction;
use App\Actions\SalvaInscritoInCompanyAction;
use App\Http\Requests\StoreInscricaoCursoRequest;
use App\Http\Requests\UpdateInscricaoCursoRequest;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\LancamentoFinanceiro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InscricaoCursoController extends Controller
{
    /**
     * Armazena o curso selecionado na sessão e redireciona ao painel
     * para o fluxo Livewire ConfirmInscricaoCurso.
     */
    public function cursoInscricao(Request $request): RedirectResponse
    {
        session()->forget(['interlab', 'curso', 'empresa']);

        session()->put(
            'curso',
            AgendaCursos::query()
                ->where('uid', $request->target)
                ->where('inscricoes', 1)
                ->firstOrFail()
        );

        if (auth()->check() && auth()->user()->pessoa === null) {
            throw new \LogicException('Usuário autenticado sem pessoa vinculada ao acessar inscrição em curso.');
        }

        return redirect()->route('painel-index');
    }

    /**
     * Adiciona inscrito manualmente na tela de agenda de cursos
     */
    public function salvaInscrito(StoreInscricaoCursoRequest $request): RedirectResponse
    {
        $agenda = AgendaCursos::query()->findOrFail($request->agenda_curso_id);

        if ($agenda->tipo_agendamento === 'IN-COMPANY') {
            app(SalvaInscritoInCompanyAction::class)->criar(
                $agenda,
                $request->nome,
                $request->email,
                $request->telefone
            );
        } else {
            app(SalvaInscritoAction::class)->criar($agenda, $request->validated());
        }

        return back()->with('success', 'Inscrito adicionado com sucesso')->withFragment('participantes');
    }

    /**
     * Edita inscrito manualmente na tela de agenda de cursos
     */
    public function atualizaInscrito(CursoInscrito $inscrito, UpdateInscricaoCursoRequest $request): RedirectResponse
    {
        $inscrito->load('agendaCurso');

        if ($inscrito->agendaCurso->tipo_agendamento === 'IN-COMPANY') {
            app(SalvaInscritoInCompanyAction::class)->atualizar($inscrito, $request->validated());
        } else {
            app(SalvaInscritoAction::class)->atualizar($inscrito, $request->validated());
        }

        return back()->with('success', 'Inscrito atualizado com sucesso')->withFragment('participantes');
    }

    /**
     * Cancela a inscrição de um participante
     */
    public function cancelaInscricao(CursoInscrito $inscrito): RedirectResponse
    {
        $inscrito->load('agendaCurso');
        $isInCompany = $inscrito->agendaCurso->tipo_agendamento === 'IN-COMPANY';

        $inscrito->delete();

        if (! $isInCompany) {
            if ($inscrito->empresa_id) {
                app(AtualizarLancamentoCursoAction::class)->execute($inscrito);
            } else {
                LancamentoFinanceiro::where('pessoa_id', $inscrito->pessoa_id)
                    ->where('agenda_curso_id', $inscrito->agenda_curso_id)
                    ->delete();
            }
        }

        return back()->with('success', 'Inscrição cancelada com sucesso!');
    }
}
