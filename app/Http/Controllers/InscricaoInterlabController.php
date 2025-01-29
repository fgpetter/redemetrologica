<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log, Validator};
use Illuminate\Http\{Request, RedirectResponse};
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
  public function confirmaInscricao(Request $request): RedirectResponse
  {
    $request->merge( return_only_nunbers( $request->only('telefone') ) );

    $validator = Validator::make($request->all(), [
        "empresa_uid" => ['required', 'exists:pessoas,uid'],
        "interlab_uid" => ['required', 'exists:agenda_interlabs,uid'],
        "encerra_cadastro" => ['required', 'integer', 'max:1', 'in:0,1'],
        "laboratorio" => ['required', 'string', 'max:191'],
        "responsavel_tecnico" => ['required', 'string', 'max:191'],
        "telefone" => ['nullable', 'string', 'min:10', 'max:11'],
        "email" => ['nullable', 'email', 'max:191'],
        "informacoes_inscricao" => ['nullable', 'string'],
        "cep" => ['required', 'string'],
        "endereco" => ['required', 'string'],
        "complemento" => ['nullable', 'string'],
        "bairro" => ['nullable', 'string'],
        "cidade" => ['nullable', 'string'],
        "uf" => ['required', 'string'],
      ],[
        'laboratorio.required' => 'Preencha o campo laboratório',
        'laboratorio.max' => 'O campo laboratório deve ter no máximo :max caracteres',
        'responsavel_tecnico.required' => 'Preencha o campo laboratório',
        'responsavel_tecnico.max' => 'O campo laboratório deve ter no máximo :max caracteres',
        'telefone.*' => 'O telefone informado é inválido',
        'email.*' => 'O email informado é inválido',
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'uf.required' => 'Preencha o campo UF',
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

      return back()->with('error', 'Houve um erro a processar os dados, tente novamente')->withErrors($validator)->withInput();
    }

    $agenda_interlab = AgendaInterlab::where('uid', $request->interlab_uid)->first();
    $empresa = Pessoa::where( 'uid', $validator->safe()->empresa_uid )->first();

    DB::transaction(function () use ($validator, $empresa, $agenda_interlab) {

      $endereco = Endereco::create([
        'pessoa_id' => $empresa->id,
        'info' => 'Laboratório: '.$validator->safe()->laboratorio,
        'cep' => $validator->safe()->cep,
        'endereco' => $validator->safe()->endereco,
        'complemento' => $validator->safe()->complemento,
        'bairro' => $validator->safe()->bairro,
        'cidade' => $validator->safe()->cidade,
        'uf' => $validator->safe()->uf,
      ]);
  
      $laboratorio = InterlabLaboratorio::create([
        'empresa_id' => $empresa->id,
        'endereco_id' => $endereco->id,
        'nome' => $validator->safe()->laboratorio,
        'responsavel_tecnico' => $validator->safe()->responsavel_tecnico,
        'telefone' => $validator->safe()->telefone,
        'email' => $validator->safe()->email,
      ]);
  
      InterlabInscrito::create([
        'pessoa_id' => auth()->user()->pessoa->id,
        'empresa_id' => $empresa->id,
        'laboratorio_id' => $laboratorio->id,
        'agenda_interlab_id' => $agenda_interlab->id,
        'data_inscricao' => now(),
        'informacoes_inscricao' => $validator->safe()->informacoes_inscricao,
      ]);

    });

    if( $request->encerra_cadastro == 1 ) {
      session()->forget(['interlab', 'empresa', 'convite']);
      return redirect('painel')->with('success', 'Inscrição realizada com sucesso!');

    } else {
      return redirect('painel')->with('success', 'Laboratório cadastrado com sucesso!');
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

    auth()->user()->pessoa->empresas()->attach($empresa->id);

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

      return back()->with('error', 'Dados informados não são válidos')->withErrors($validator);
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
    
    return back()->with('success', 'Dados salvos com sucesso!');
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
    return back()->with('success', 'Inscrição cancelada com sucesso!');
  }

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

      $empresa_participante = InterlabInscrito::where('empresa_id', $empresa->id)
        ->where('agenda_interlab_id', $agenda_interlab->id)
        ->with('pessoa')
        ->get();

      $observacoes = '';
      foreach($empresa_participante as $empresa) {
        $data = Carbon::parse($empresa->data_inscricao)->format('d/m/Y H:i');
        $observacoes .= "Inscrição de {$laboratorio->nome}, com valor de R$ {$empresa->valor}, em {$data} \n";
      }

      $lancamento->update([
        'valor' => $empresa_participante->sum('valor'),
        'observacoes' => $observacoes
      ]);
    }

  }

}
