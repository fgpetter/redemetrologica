<?php

namespace App\Http\Controllers;

use App\Models\InterlabAnalista;
use Illuminate\Support\Facades\Mail;
use App\Actions\CriarEnviarSenhaAction;
use App\Actions\InscricaoInterlabAction;
use App\Mail\NovoCadastroInterlabNotification;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{Log, Validator};
use App\Mail\ConfirmacaoInscricaoInterlabNotification;
use App\Http\Requests\ConfirmaInscricaoInterlabRequest;
use App\Actions\Financeiro\GerarLancamentoInterlabAction;
use App\Models\{AgendaInterlab, Pessoa, InterlabInscrito};

class InscricaoInterlabController extends Controller
{
  /**
   * Verifica o link de inscrição e faz o roteamento da inscrição 
   * salvando dados do link na sessão e redirecionando para o painel
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function interlabInscricao(Request $request): RedirectResponse
  {
    // limpa a sessao
    session()->forget(['interlab','curso', 'empresa', 'convite']);

    // salva dados da inscrição na sessão
    session()->put('interlab', AgendaInterlab::where('uid', $request->target)->where('inscricao', 1)->firstOrFail());
    
    // redireciona para o painel e carrega a lógica do componente em app\view\ConfirmaInscricao
    return redirect('painel');
  }

  /**
   * Cadastra pessoa ou laboratório no PEP a partir da área do cliente (painel)
   *
   * @param Request $request
   * @return RedirectResponse
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

    Mail::to($inscrito->pessoa->email)
      ->cc('sistema@redemetrologica.com.br')
      ->send(new ConfirmacaoInscricaoInterlabNotification($inscrito, $agenda_interlab));

    if ($agenda_interlab->status === 'CONFIRMADO' && ! empty($agenda_interlab->interlab?->tag)) {
      app(CriarEnviarSenhaAction::class)->execute($inscrito, 1);
    }

    if ($request->encerra_cadastro == 1) {
      session()->forget(['interlab', 'empresa', 'convite']);

      return back()->with('success', 'Inscrição realizada com sucesso!');
    }

    return back()->with('success', 'Laboratório cadastrado com sucesso!')->withFragment('participantes');
  }

  /**
   * Adiciona / Edita inscrito manualmente na tela de agenda de interlabs
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function salvaInscrito(Request $request, InterlabInscrito $inscrito): RedirectResponse
  {
    // valida valor e informacoes_inscricao
    $validator = Validator::make($request->all(), [
      'valor' => ['nullable', 'string'],
      'informacoes_inscricao' => ['nullable', 'string', 'max:1000'],
    ],[
      'valor.string' => 'O valor digitado é inválido',
      'informacoes_inscricao.max' => 'As informações não podem ter mais que :max caracteres'
    ]);

    if($validator->fails()){
      Log::channel('validation')->info("Erro de validação", 
      [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) .'::'. __FUNCTION__ ,
          'errors' => $validator->errors() ?? null,
      ]);

      return back()->with('error', 'Dados informados não são válidos')->withErrors($validator)->withFragment('participantes');
    }

    // atualiza dados de inscrito
    $inscrito->update([
      'valor' => formataMoeda( $request->valor ),
      'informacoes_inscricao' => $validator->safe()->string('informacoes_inscricao')
    ]);
    // se valor > 0 atualiza lancamento financeiro
    if($request->valor > 0) {
      app(GerarLancamentoInterlabAction::class)->execute($inscrito, $request->valor);
    }
    
    return back()->with('success', 'Dados salvos com sucesso!')->withFragment('participantes');
  }

  /**
   * Cancela a inscrição de um participante
   *
   * @param InterlabInscrito $inscrito
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
