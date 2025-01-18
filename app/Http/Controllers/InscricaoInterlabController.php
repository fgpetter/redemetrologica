<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\{Request, RedirectResponse};
use App\Models\{AgendaInterlab, Pessoa, CentroCusto, Interlab, InterlabInscrito, LancamentoFinanceiro, Convite};

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
    session()->put('interlab', AgendaInterlab::where('uid', $request->target)->where('inscricao', 1)->firstOrFail() );
    
    // verifica se é um convite e salva informações na sessão
    if($request->referer) {
      session()->put('empresa', Pessoa::where('uid', $request->referer)->where('tipo_pessoa', 'PJ')->firstOrfail());
      session()->put('convidado', true);
      session()->put('convite-email', true);
    }

    // redireciona para o painel e carrega a lógica do componente em app\view\ConfirmaInscricao
    return redirect('painel');

  }

  /**
   * Cadastra cliente no curso a partir da área do cliente
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function confirmaInscricao(Request $request): RedirectResponse
  {
    // valida dados
    $validator = Validator::make($request->all(), [
      'interlab_uid' => ['required', 'string', 'exists:agenda_interlabs,uid'],
      'inscrever_usuario_logado' => ['nullable', 'integer', 'in:1'],
      'indicacao_nome' => ['nullable', 'array'],
      'indicacao_nome.*' => ['nullable', 'string', 'max:190'],
      'indicacao_email' => ['nullable', 'array'],
      'indicacao_email.*' => ['nullable', 'email', 'max:190'],
    ]);

    if($validator->fails()){
      Log::channel('validation')->info("Erro de validação", 
      [
        'user' => auth()->user() ?? null,
        'request' => $request->all() ?? null,
        'errors' => $validator->errors() ?? null,
      ]);

      return back()->with('error', 'Houve um erro a processar os dados, tente novamente')->with('errors', $validator->errors());
    }

    if( !$request->inscrever_usuario_logado && !$request->indicacao_nome[0] ) {
      return back()->with('error', 'Impossivel enviar uma inscrição vazia');
    }

    $agenda_interlab = AgendaInterlab::where('uid', $request->interlab_uid)->first();
    $empresa = auth()->user()->pessoa->empresas()->first() ?? null;

    // verifica se tem empresa atrelada
    if( !$empresa ) {
      return back()->with('error', 'Necessário informar uma empresa para inscrição');
    }

    // verifica se a empresa já tem cadastrados no intelab, se não cria
    InterlabInscrito::firstOrCreate([
      'pessoa_id' => $empresa->id,
      'agenda_interlab_id' => $agenda_interlab->id
      ],[
        'data_inscricao' => now()
      ]);

    // verifica se usuario se inscreveu
    if( $request->inscrever_usuario_logado == 1 ) {
      // adiciona inscrito ao interlab
      $inscrito = InterlabInscrito::create([
          'pessoa_id' => auth()->user()->pessoa->id,
          'agenda_interlab_id' => $agenda_interlab->id,
          'empresa_id' => $empresa->id,
          'data_inscricao' => now()
        ]);
  
      // adiciona informacoes financeiras
      $this->adicionaLancamentoFinanceiro($agenda_interlab, $empresa);
    }

    // envia convites se existir
    $this->enviaConvite($request, $agenda_interlab);

    if( isset($inscrito) && $inscrito ) {
      // remove dados da sessão
      session()->forget(['interlab', 'empresa', 'convite']);

      // redireciona para painel
      return redirect('painel')->with('success', 'Inscrição realizada com sucesso!');

    } else {
      // adiciona informacao de convites enviados na sessão
      session()->put('convites_enviados', true);
      return redirect('painel')->with('success', 'Convites enviados com sucesso!');
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

    auth()->user()->pessoa->empresas()->sync($empresa->id);

    return back()->with('success', 'Empresa adicionada com sucesso!');
  }

  /**
   * Adiciona / Edita inscrito manualmente na tela de agenda de interlabs
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function salvaInscrito(Request $request): RedirectResponse
  {
    $validator = Validator::make($request->all(), [
      'inscrito_uid' => ['nullable', 'exists:curso_inscritos,uid'],
      'pessoa_uid' => ['nullable', 'exists:pessoas,uid'],
      'nome' => ['required', 'string', 'max:190'],
      'telefone' => ['required', 'celular_com_ddd'],
      'email' => ['required', 'email', 'max:190'],
      'cpf_cnpj' => ['required_if:pessoa_uid,null', 'cpf'],
      'data_confirmacao' => ['nullable', 'date'],
      'certificado_emitido' => ['nullable', 'date'],
      'resposta_pesquisa' => ['nullable', 'date'],
      ],[
        'inscrito_uid.exists' => 'Foi enviado um dado inválido atualize a pagina e tente novamente',
        'pessoa_uid.exists' => 'Foi enviado um dado inválido atualize a pagina e tente novamente',
        'cpf_cnpj.required_if' => 'Foi enviado um dado inválido atualize a pagina e tente novamente',
        'nome.required' => 'O nome precisa ser informado',
        'nome.string' => 'O nome precisa ser umtexto válido',
        'nome.max' => 'O nome deve ter no maximo 190 caracteres',
        'email.required' => 'O email precisa ser informado',
        'email.email' => 'O email precisa ser um email válido',
        'email.max' => 'O email deve ter no maximo 190 caracteres',
        'cpf_cnpj.required_if' => 'O documento precisa ser informado',
        'cpf_cnpj.cpf' => 'O dado enviado não é um CPF válido',
        'data_confirmacao.date' => 'O dado enviado não é uma data válida',
        'certificado_emitido.date' => 'O dado enviado não é uma data válida',
        'resposta_pesquisa.date' => 'O dado enviado não é uma data válida',
      ]);

    if($validator->fails()){
      Log::channel('validation')->info("Erro de validação", 
      [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'errors' => $validator->errors() ?? null,
      ]);

      return back()->with('error', 'Dados informados não são válidos')->withErrors($validator);
    }

    if($request->pessoa_uid) {
      $pessoa = Pessoa::where('uid', $request->pessoa_uid)->first();
      $pessoa->update([
        'nome_razao' => $request->nome,
        'email' => $request->email,
        'telefone' => $request->telefone,
      ]);
    } else {
      $pessoa = Pessoa::create([
        'uid' => $request->pessoa_uid ?? config('hashing.uid'),
        'nome_razao' => $request->nome,
        'email' => $request->email,
        'telefone' => $request->telefone,
        'cpf' => $request->cpf_cnpj,
        'tipo_pessoa' => 'PF',
      ]);
    }

    $inscrito = InterlabInscrito::where('uid', $request->inscrito_uid)->first();
    $this->adicionaInscrito([
      'uid' => $request->inscrito_uid,
      'pessoa_id' => $pessoa->id,
      'empresa_id' => $inscrito?->empresa_id ?? null,
      'agenda_curso_id' => $inscrito?->agenda_curso_id ?? null,
      'data_inscricao' => $inscrito?->data_inscricao ?? null,
      'certificado_emitido' => $request->certificado_emitido,
      'resposta_pesquisa' => $request->resposta_pesquisa,
    ]);
    
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
   * Salva informações de convites no banco
   *
   * @param Request $request
   * @return void
   */
  private function enviaConvite(Request $request, $agenda_interlab): void
  {
    // salva convites no banco de dados
    foreach($request['indicacao_nome'] as $key => $nome) {

      // pula se o email não for revalidado
      if(preg_match("/^[0-9a-z]([-_.]*?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,9}$/", $request['indicacao_email'][$key], $matches) == 0) {
        continue;
      }
      // evita envio de convite duplicado
      Convite::firstOrCreate([
        'agenda_interlab_id' => $agenda_interlab->id,
        'email' => $request['indicacao_email'][$key],
      ],[
        'pessoa_id' => auth()->user()->pessoa->id,
        'nome' => $nome,
      ]);
    }
  }

  
  /**
   * Adiciona / Edita lançamentos financeiros referentes a inscrição no interlab
   *
   * @param AgendaInterlab $agenda_interlab
   * @param Pessoa $empresa
   * @param float|null $valor
   * @return void
   */
  private function adicionaLancamentoFinanceiro(AgendaInterlab $agenda_interlab, Pessoa $empresa, $valor = null)
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
      ]);
    } else { // se a empresa já possui inscritos nesse interlab, atualiza o valor

      $inscritos_empresa = InterlabInscrito::where('empresa_id', $empresa->id)
        ->where('agenda_interlab_id', $agenda_interlab->id)
        ->with('pessoa')
        ->get();

      $observacoes = '';
      foreach($inscritos_empresa as $inscrito) {
        $data = Carbon::parse($inscrito->data_inscricao)->format('d/m/Y H:i');
        $observacoes .= "Inscrição de {$inscrito->pessoa->nome_razao}, com valor de R$ {$inscrito->valor}, em {$data} \n";
      }

      $lancamento->update([
        'valor' => $inscritos_empresa->sum('valor'),
        'observacoes' => $observacoes
      ]);
    }

  }


}
