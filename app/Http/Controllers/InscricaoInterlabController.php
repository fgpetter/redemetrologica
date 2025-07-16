<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NovoCadastroInterlabNotification;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{DB, Log, Validator};
use App\Mail\ConfirmacaoInscricaoInterlabNotification;
use App\Http\Requests\ConfirmaInscricaoInterlabRequest;
use App\Models\{AgendaInterlab, Pessoa, InterlabInscrito, LancamentoFinanceiro, InterlabLaboratorio, Endereco, Laboratorio};

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
   * Cadastra pessoa ou laboratório no PEP a partir da área do cliente
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
        'info' => 'Laboratório: '.$validated['laboratorio'],
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
        'responsavel_tecnico' => $validated['responsavel_tecnico'],
        'telefone' => $validated['telefone'],
        'email' => $validated['email'],
      ]);
  
      $inscrito = InterlabInscrito::create([
        'pessoa_id' => $responsavel->id ?? auth()->user()->pessoa->id,
        'empresa_id' => $empresa->id,
        'laboratorio_id' => $laboratorio->id,
        'agenda_interlab_id' => $agenda_interlab->id,
        'data_inscricao' => now(),
        'valor' => $validated['valor'] ?? null,
        'informacoes_inscricao' => $validated['informacoes_inscricao'],
      ]);

      return $inscrito;

    });

    if(!$inscrito){
      return back()->with('error', 'Erro ao processar os dados')->withFragment('participantes');
    }

    if( isset($request->valor) && $request->valor > 0) {
      $this->adicionaLancamentoFinanceiro($inscrito->agendaInterlab, $inscrito->empresa, $inscrito->laboratorio , $request->valor);
    }

    Mail::to('interlab@redemetrologica.com.br')
      ->cc('bonus@redemetrologica.com.br')
      ->cc('sistema@redemetrologica.com.br')
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
   * Adiciona empresa contratante a inscricao do interlab
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function informaEmpresa(Request $request): RedirectResponse
  {
    // valida dados
    $request->validate([
      'cnpj' => ['nullable', 'cnpj'],
      ],[
      'cnpj.cnpj' => 'O dado enviado não é um CNPJ válido',
    ]);

    $empresa = Pessoa::where('cpf_cnpj', preg_replace('/[^0-9]/', '', $request->cnpj))->where('tipo_pessoa', 'PJ')->first() ?? null;
    
    if(!$empresa) {
      return back()->with('error', 'Empresa não encontrada');
    }

    auth()->user()->pessoa->empresas()->syncWithoutDetaching([$empresa->id]);

    return back()->with('success', 'Empresa adicionada com sucesso!');
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
      $this->adicionaLancamentoFinanceiro($inscrito->agendaInterlab, $inscrito->empresa, $inscrito->laboratorio , $request->valor);
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
    $inscrito->delete();
    $this->atualizaFinanceiro($inscrito);
    return back()->with('success', 'Inscrição cancelada com sucesso!')->withFragment('participantes');
  }

  /**
   * Reseta todos os dados da sessão e encerra a inscrição
   *
   * @return void
   */
  public function limpaSessao()
  {
    // remove dados da sessão
    session()->forget(['interlab', 'empresa', 'convite']);

    // redireciona para painel
    return redirect('painel');
    
  }

  /**
   * Adiciona / Edita lançamentos financeiros referentes a inscrição no interlab
   *
   * @param AgendaInterlab $agenda_interlab
   * @param Pessoa $empresa
   * @param float|null $valor
   * @return void
   */
  private function adicionaLancamentoFinanceiro(AgendaInterlab $agenda_interlab, Pessoa $empresa, InterlabLaboratorio $laboratorio, $valor = null)
  {
    $lancamento = LancamentoFinanceiro::where('pessoa_id', $empresa->id)
      ->where('agenda_interlab_id', $agenda_interlab->id)
      ->first();
    
    // se a empresa não possui inscritos nesse interlab, cria um novo lançamento
    if(!$lancamento) {
      LancamentoFinanceiro::create([
        'pessoa_id' => $empresa->id,
        'agenda_interlab_id' => $agenda_interlab->id,
        'historico' => 'Inscrição no interlab - ' . $agenda_interlab->interlab->nome,
        'valor' => formataMoeda($valor),
        'centro_custo_id' => '4', // INTERLABORATORIAL
        'plano_conta_id' => '3', // RECEITA PRESTAÇÃO DE SERVIÇOS
        'data_emissao' => now(),
        'status' => 'PROVISIONADO',
        'observacoes' => "Inscrição de {$laboratorio->nome}, com valor de R$ {$valor} \n"
      ]);
    } else { // se a empresa já possui inscritos nesse interlab, atualiza o valor

      $inscricoes_empresa = InterlabInscrito::where('empresa_id', $empresa->id)
        ->where('agenda_interlab_id', $agenda_interlab->id)
        ->whereNotNull('valor')
        ->with('pessoa')
        ->get();

      $observacoes = '';
      foreach($inscricoes_empresa as $incricao) {
        $data = Carbon::parse($incricao->data_inscricao)->format('d/m/Y H:i');
        $observacoes .= "Inscrição de {$incricao->laboratorio->nome}, com valor de R$ {$incricao->valor}, em {$data} \n";
      }

      $lancamento->update([
        'valor' => $inscricoes_empresa->sum('valor'),
        'observacoes' => $observacoes
      ]);
    }

  }

  /**
   * Attualiza lançamento financeiro removendo o valor da inscrição
   * deletada
   *
   * @param InterlabInscrito $inscrito
   * @return void
   */
  private function atualizaFinanceiro(InterlabInscrito $inscrito): void
  {
    $lancamento = LancamentoFinanceiro::where('pessoa_id', $inscrito->empresa_id)
      ->where('agenda_interlab_id', $inscrito->agenda_interlab_id)
      ->first();

    if( $lancamento ) {
      $inscricoes_empresa = InterlabInscrito::where('empresa_id', $inscrito->empresa_id)
        ->where('agenda_interlab_id', $inscrito->agenda_interlab_id)
        ->whereNotNull('valor')
        ->with('pessoa')
        ->get();

      $observacoes = '';
      foreach($inscricoes_empresa as $incricao) {
        $data = Carbon::parse($incricao->data_inscricao)->format('d/m/Y H:i');
        $observacoes .= "Inscrição de {$incricao->laboratorio->nome}, com valor de R$ {$incricao->valor}, em {$data} \n";
      }

      $lancamento->update([
        'valor' => $inscricoes_empresa->sum('valor'),
        'observacoes' => $observacoes
      ]);
    }
  }


}
