<?php

namespace App\Http\Controllers;

use App\Actions\CriarEnviarSenhaInterlabAction;
use App\Actions\Financeiro\GerarLancamentoInterlabAction;
use App\Actions\InscricaoInterlabAction;
use App\Exceptions\InvalidEmailException;
use App\Http\Requests\ConfirmaInscricaoInterlabRequest;
use App\Mail\ConfirmacaoInscricaoInterlabNotification;
use App\Mail\NovoCadastroInterlabNotification;
use App\Models\AgendaInterlab;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\Pessoa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InscricaoInterlabController extends Controller
{
    /**
     * Verifica o link de inscrição e faz o roteamento da inscrição
     * salvando dados do link na sessão e redirecionando para o painel
     */
    public function interlabInscricao(Request $request): RedirectResponse
    {
        // limpa a sessao
        session()->forget(['interlab', 'curso', 'empresa']);

        // salva dados da inscrição na sessão
        session()->put('interlab', AgendaInterlab::where('uid', $request->target)->where('inscricao', 1)->firstOrFail());

        // redireciona ao painel: com session('interlab'), o index renderiza nova-inscricao-pd e os Livewire de interlab (BuscaCNPJ, LabInscritos, etc.)
        return redirect('painel');
    }

    /**
     * Cadastra pessoa ou laboratório no PEP a partir da área do cliente (painel)
     *
     * @param  Request  $request
     */
    public function confirmaInscricao(ConfirmaInscricaoInterlabRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $agenda_interlab = AgendaInterlab::where('uid', $validated['interlab_uid'])->first();
        $empresa = Pessoa::where('uid', $validated['empresa_uid'])->first();
        $responsavel = Pessoa::where('uid', $validated['pessoa_uid'])->first();

        $dados = [
            'empresa_id' => $empresa->id,
            'pessoa_id' => $responsavel->id ?? auth()->user()->pessoa->id,
            'laboratorio_id' => null,
            'laboratorio' => [
                'nome' => $validated['laboratorio'],
                'responsavel_tecnico' => $validated['responsavel_tecnico'],
                'telefone' => $validated['telefone'] ?? null,
                'email' => $validated['email'],
                'endereco' => [
                    'cep' => $validated['cep'],
                    'endereco' => $validated['endereco'],
                    'complemento' => $validated['complemento'] ?? null,
                    'bairro' => $validated['bairro'],
                    'cidade' => $validated['cidade'],
                    'uf' => $validated['uf'],
                ],
            ],
            'valor' => $validated['valor'] ?? null,
            'informacoes_inscricao' => $validated['informacoes_inscricao'] ?? '',
        ];

        $analistas = $request->analistas ?? [];

        $inscrito = app(InscricaoInterlabAction::class)->execute($agenda_interlab, $dados, $analistas);

        if (! $inscrito) {
            return back()->with('error', 'Erro ao processar os dados')->withFragment('participantes');
        }

        if (isset($request->valor) && $request->valor > 0) {
            app(GerarLancamentoInterlabAction::class)->execute($inscrito, $request->valor);
        }

        Mail::to('interlab@redemetrologica.com.br')
            ->cc('tecnico@redemetrologica.com.br')
            ->send(new NovoCadastroInterlabNotification($inscrito, $agenda_interlab));

        if (empty($inscrito->pessoa->email)) {
            $content = [
                'class' => self::class,
                'inscrito_id' => $inscrito->id,
                'inscrito_pessoa_uid' => $inscrito->pessoa?->id ?? '',
            ];
            new InvalidEmailException($content);
        } else {
            Mail::to($inscrito->pessoa->email)
                ->cc('sistema@redemetrologica.com.br')
                ->send(new ConfirmacaoInscricaoInterlabNotification($inscrito, $agenda_interlab));
        }

        if ($agenda_interlab->status === 'CONFIRMADO' && ! empty($agenda_interlab->interlab?->tag)) {
            app(CriarEnviarSenhaInterlabAction::class)->execute($inscrito, 15);
        }

        if ($request->encerra_cadastro == 1) {
            session()->forget(['interlab', 'empresa']);

            return back()->with('success', 'Inscrição realizada com sucesso!');
        }

        return back()->with('success', 'Laboratório cadastrado com sucesso!')->withFragment('participantes');
    }

    /**
     * Cancela a inscrição de um participante
     *
     * @return RedirectResponse
     */
    public function cancelaInscricao(InterlabInscrito $inscrito)
    {
        // Deleta analistas vinculados a esta inscrição
        InterlabAnalista::where('interlab_inscrito_id', $inscrito->id)->delete();

        // Cancela o lançamento financeiro antes de apagar o inscrito
        app(GerarLancamentoInterlabAction::class)->cancelarLancamento($inscrito);

        // Deleta inscrito
        $inscrito->delete();

        return back()->with('success', 'Inscrição cancelada com sucesso!')->withFragment('participantes');
    }
}
