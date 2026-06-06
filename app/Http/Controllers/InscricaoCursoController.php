<?php

namespace App\Http\Controllers;

use App\Actions\AtualizarInscritoCursoAction;
use App\Actions\Financeiro\AtualizarLancamentoCursoAction;
use App\Actions\Financeiro\GerarLancamentoCursoAction;
use App\Actions\InscreverParticipanteCursoAction;
use App\Http\Requests\ConfirmaInscricaoRequest;
use App\Http\Requests\StoreInscricaoCursoRequest;
use App\Http\Requests\UpdateInscricaoCursoRequest;
use App\Models\AgendaCursos;
use App\Models\Convite;
use App\Models\CursoInscrito;
use App\Models\Endereco;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscricaoCursoController extends Controller
{
    /**
     * Verifica o link de inscrição e faz o roteamento da inscrição
     * salvando dados do link na sessão e redirecionando para o painel
     */
    public function cursoInscricao(Request $request): RedirectResponse
    {
        // limpa a sessao com dados de cursos visitados
        session()->forget(['interlab', 'curso', 'empresa', 'convite']);

        // salva informações do curso na sessão ou aborta 404
        session()->put('curso', AgendaCursos::where('uid', $request->target)->where('inscricoes', 1)->firstOrFail());

        // verifica se é um convite e salva informações na sessão
        if ($request->referer) {
            session()->put('empresa', Pessoa::where('uid', $request->referer)->where('tipo_pessoa', 'PJ')->first() ?? null);
            session()->put('convidado', true);
            session()->put('convite-email', true);
        }

        if (auth()->check() && auth()->user()->pessoa === null) {
            throw new \LogicException('Usuário autenticado sem pessoa vinculada ao acessar inscrição em curso.');
        }

        return redirect('painel');
    }

    /**
     * Cadastra cliente no curso a partir da área do cliente
     *
     * @param  Request  $request
     */
    public function confirmaInscricao(ConfirmaInscricaoRequest $request): RedirectResponse
    {
        $pessoaUsuario = auth()->user()->pessoa;
        if ($pessoaUsuario === null) {
            throw new \LogicException('Usuário autenticado sem pessoa vinculada ao confirmar inscrição em curso.');
        }

        $agendacurso = AgendaCursos::where('id', $request->id_curso)->with('curso')->firstOrFail();
        $empresa = $request->id_empresa ? Pessoa::where('id', $request->id_empresa)->first() : null;

        $associado = $empresa
            ? ($empresa->associado ?? null)
            : ($pessoaUsuario->associado ?? null);

        DB::transaction(function () use ($request, $agendacurso, $pessoaUsuario, $empresa, $associado) {
            if ($empresa) {
                $pessoaUsuario->empresas()->sync([$empresa->id]);
            }

            $pessoaUsuario->update([
                'nome_razao' => $request->nome,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'cpf_cnpj' => $request->cpf_cnpj,
            ]);

            if (! $empresa) {
                Endereco::updateOrCreate(
                    [
                        'pessoa_id' => $pessoaUsuario->id,
                    ],
                    [
                        'cep' => $request->cep,
                        'uf' => $request->uf,
                        'endereco' => $request->endereco,
                        'complemento' => $request->complemento,
                        'bairro' => $request->bairro,
                        'cidade' => $request->cidade,
                    ]
                );
            }

            $nome = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($request->nome));
            $email = strtolower(trim($request->email));

            $cursoInscrito = CursoInscrito::updateOrCreate(
                [
                    'pessoa_id' => $pessoaUsuario->id,
                    'agenda_curso_id' => $agendacurso->id,
                ],
                [
                    'empresa_id' => $empresa?->id,
                    'nome' => $nome,
                    'email' => $email,
                    'telefone' => $request->telefone,
                    'valor' => ($associado) ? $agendacurso->investimento_associado : $agendacurso->investimento,
                    'data_inscricao' => now(),
                ]
            );

            $lancamento = app(GerarLancamentoCursoAction::class)->execute(
                $agendacurso,
                $pessoaUsuario,
                $empresa,
                (bool) $associado,
                null,
                $nome
            );

            $cursoInscrito->update([
                'lancamento_financeiro_id' => $lancamento->id,
            ]);
        });

        if ($request->convidado || ! $empresa) {
            session()->forget(['curso', 'empresa', 'convite']);
        }

        return redirect('painel');
    }

    /**
     * Adiciona empresa contratante a inscricao do curso
     */
    public function informaEmpresa(Request $request): RedirectResponse
    {
        // valida dados
        $request->validate([
            'cnpj' => ['required', 'cnpj'],
        ], [
            'cnpj.required' => 'Digite o CNPJ da empresa',
            'cnpj.cnpj' => 'O dado enviado não é um CNPJ válido',
        ]);

        $empresa = Pessoa::where('cpf_cnpj', preg_replace('/[^0-9]/', '', $request->cnpj))->where('tipo_pessoa', 'PJ')->first() ?? null;

        if (! $empresa) {
            return back()->with('error', 'Empresa não encontrada');
        }

        auth()->user()->pessoa->empresas()->syncWithoutDetaching([$empresa->id]);

        return back()->with('success', 'Empresa adicionada com sucesso!');
    }

    /**
     * Salva informações de convites no banco e envia emails aos
     * inscritos
     */
    public function enviaConvite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_curso' => ['required', 'exists:agenda_cursos,id'],
            'id_pessoa' => ['required', 'exists:pessoas,id'],
            'indicacao-nome' => ['required', 'array'],
            'indicacao-nome.*' => ['required', 'string', 'max:190'],
            'indicacao-email' => ['required', 'array'],
            'indicacao-email.*' => ['required', 'email', 'max:190'],
        ]);

        if (Pessoa::find($validated['id_pessoa'])->empresas()->doesntExist()) {
            return back()->with('error', 'Você precisa adicionar uma empresa para enviar convites');
        }

        // salva convites no banco de dados
        foreach ($validated['indicacao-nome'] as $key => $nome) {

            // pula se o email não for revalidado
            if (preg_match('/^[0-9a-z]([-_.]*?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,9}$/', $validated['indicacao-email'][$key], $matches) == 0) {
                continue;
            }
            // evita envio de convite duplicado
            Convite::firstOrCreate([
                'agenda_curso_id' => $validated['id_curso'],
                'email' => $validated['indicacao-email'][$key],
            ], [
                'pessoa_id' => $validated['id_pessoa'],
                'nome' => $nome,
            ]);
        }

        return back()->with('success', 'Convites enviados com sucesso!');

    }

    /**
     * Adiciona inscrito manualmente na tela de agenda de cursos
     */
    public function salvaInscrito(StoreInscricaoCursoRequest $request): RedirectResponse
    {
        app(InscreverParticipanteCursoAction::class)->execute($request->validated());

        return back()->with('success', 'Inscrito adicionado com sucesso')->withFragment('participantes');
    }

    /**
     * Edita inscrito manualmente na tela de agenda de cursos
     */
    public function atualizaInscrito(CursoInscrito $inscrito, UpdateInscricaoCursoRequest $request): RedirectResponse
    {
        app(AtualizarInscritoCursoAction::class)->execute($inscrito, $request->validated());

        return back()->with('success', 'Inscrito atualizado com sucesso')->withFragment('participantes');
    }

    /**
     * Cancela a inscrição de um participante
     */
    public function cancelaInscricao(CursoInscrito $inscrito): RedirectResponse
    {
        $inscrito->delete();

        if ($inscrito->empresa_id) {
            app(AtualizarLancamentoCursoAction::class)->execute($inscrito);
        } else {
            LancamentoFinanceiro::where('pessoa_id', $inscrito->pessoa_id)
                ->where('agenda_curso_id', $inscrito->agenda_curso_id)
                ->delete();
        }

        return back()->with('success', 'Inscrição cancelada com sucesso!');
    }

    /**
     * Conclui o processo de inscrição removendo da sessão
     * todos os dados relacionados ao curso
     */
    public function concluiInscricao(): RedirectResponse
    {
        session()->forget(['curso', 'empresa', 'convite']);

        return back()->with('success', 'Processo de inscrição concluido com sucesso!');
    }
}
