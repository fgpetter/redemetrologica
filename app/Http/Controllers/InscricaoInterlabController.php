<?php

namespace App\Http\Controllers;

use App\Models\InterlabAnalista;
use Illuminate\Support\Facades\Mail;
use App\Actions\CriarEnviarSenhaAction;
use App\Mail\NovoCadastroInterlabNotification;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{DB, Log, Validator};
use App\Mail\ConfirmacaoInscricaoInterlabNotification;
use App\Http\Requests\ConfirmaInscricaoInterlabRequest;
use App\Actions\Financeiro\GerarLancamentoInterlabAction;
use App\Models\{AgendaInterlab, Pessoa, InterlabInscrito, LancamentoFinanceiro, InterlabLaboratorio, Endereco};

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
    $empresa = Pessoa::where( 'uid', $validated['empresa_uid'] )->first();
    $responsavel = Pessoa::where( 'uid', $validated['pessoa_uid'] )->first();
    $inscrito = DB::transaction(function () use ($validated, $empresa, $agenda_interlab, $responsavel) {

      $endereco = Endereco::create([
        'pessoa_id' => $empresa->id,
        'info' => 'Laboratório: ' . $validated['laboratorio'] . ' | inscrito no PEP: ' . $agenda_interlab->interlab->nome, //Adiciona info do laboratório e do interlab
        'cep' => $validated['cep'],
        'endereco' => $validated['endereco'],
        'complemento' => $validated['complemento'],
        'bairro' => $validated['bairro'],
        'cidade' => $validated['cidade'],
        'uf' => $validated['uf'],
      ]);
  
      $laboratorio = InterlabLaboratorio::create([
        'empresa_id' => $empresa->id,
        'endereco_id' => $endereco->id,
        'nome' => $validated['laboratorio'],
      ]);

      $senha = InterlabInscrito::geraTagSenha($agenda_interlab);

      $inscrito = InterlabInscrito::create([
        'pessoa_id' => $responsavel->id ?? auth()->user()->pessoa->id,
        'empresa_id' => $empresa->id,
        'laboratorio_id' => $laboratorio->id,
        'agenda_interlab_id' => $agenda_interlab->id,
        'data_inscricao' => now(),
        'valor' => $validated['valor'] ?? null,
        'informacoes_inscricao' => $validated['informacoes_inscricao'],
        'tag_senha' => $senha,
        'responsavel_tecnico' => $validated['responsavel_tecnico'],
        'telefone' => $validated['telefone'],
        'email' => $validated['email'],
      ]);

      // Salva analistas se o tipo de avaliação for ANALISTA
      if (($agenda_interlab->interlab->avaliacao ?? null) === 'ANALISTA' && $request->has('analistas')) {
        foreach ($request->analistas as $analistaData) {
          if (!empty($analistaData['nome'])) { // Validação básica pois o Request já deve validar
            InterlabAnalista::create([
              'agenda_interlab_id' => $agenda_interlab->id,
              'interlab_laboratorio_id' => $laboratorio->id,
              'nome' => $analistaData['nome'],
              'email' => $analistaData['email'] ?? '',
              'telefone' => preg_replace('/\D/', '', $analistaData['telefone'] ?? ''),
            ]);
          }
        }
      }

      if ($agenda_interlab->status === 'CONFIRMADO' && !empty($agenda_interlab->interlab->tag)) {
        app(CriarEnviarSenhaAction::class)->execute($inscrito, 1);
      }

      return $inscrito;

    });

    if(!$inscrito){
      return back()->with('error', 'Erro ao processar os dados')->withFragment('participantes');
    }

    if( isset($request->valor) && $request->valor > 0) {
      app(GerarLancamentoInterlabAction::class)->execute($inscrito, $request->valor);
    }

    Mail::to('interlab@redemetrologica.com.br')
      ->cc('tecnico@redemetrologica.com.br')
      ->send(new NovoCadastroInterlabNotification($inscrito, $agenda_interlab));

    Mail::to($inscrito->pessoa->email)
      ->cc('sistema@redemetrologica.com.br')
      ->send(new ConfirmacaoInscricaoInterlabNotification($inscrito, $agenda_interlab));

    if( $request->encerra_cadastro == 1 ) {
      session()->forget(['interlab', 'empresa', 'convite']);
      return back()->with('success', 'Inscrição realizada com sucesso!');

    } else {
      return back()->with('success', 'Laboratório cadastrado com sucesso!')->withFragment('participantes');
    }
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
