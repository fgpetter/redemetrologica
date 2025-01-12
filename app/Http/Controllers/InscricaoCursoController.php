<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use App\Http\Requests\ConfirmaInscricaoRequest;
use Illuminate\Http\{Request, RedirectResponse};
use App\Models\{Pessoa, AgendaCursos, CursoInscrito, Endereco, LancamentoFinanceiro, Convite};

class InscricaoCursoController extends Controller
{
  /**
   * Verifica o link de inscrição e faz o roteamento da inscrição 
   * salvando dados do link na sessão e redirecionando para o painel
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function cursoInscricao(Request $request): RedirectResponse
  {
    // limpa a sessao com dados de cursos visitados
    session()->forget(['interlab','curso', 'empresa', 'convite']);
    
    // salva informações do curso na sessão ou aborta 404
    session()->put('curso', AgendaCursos::where('uid', $request->target)->where('inscricoes', 1)->firstOrFail());

    // verifica se é um convite e salva informações na sessão
    if($request->referer) {
      session()->put('empresa', Pessoa::where('uid', $request->referer)->where('tipo_pessoa', 'PJ')->first() ?? null);
      session()->put('convidado', true);
    }

    return redirect('painel');
  }

  /**
   * Cadastra cliente no curso a partir da área do cliente
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function confirmaInscricao(ConfirmaInscricaoRequest $request): RedirectResponse
  {
    $agendacurso = AgendaCursos::where('id', $request->id_curso)->with('curso')->first();
    $inscrito = Pessoa::where('id', $request->id_pessoa)->first();
    $empresa = Pessoa::where('id', $request->id_empresa)->first() ?? null;
    $associado = $empresa->associado ?? null;
    
    // atribui valor de associado ao inscrito
    if($empresa){
      $associado = $empresa->associado ?? null;
    } else {
      $associado = $inscrito->associado ?? null;
    }

    // verifica se a empresa já tem cadastro no curso, se não cria
    // atribui empresa ao cadastro da pessoa caso não haja
    if($empresa){
      CursoInscrito::firstOrCreate([
        'pessoa_id' => $request->id_empresa,
        'agenda_curso_id' => $request->id_curso
        ],[
          'data_inscricao' => now()
        ]);

        $inscrito->empresas()->sync($empresa->id);
    }

    // atualiza dados da do inscrito na tabela de pessoas
    $inscrito->update([
      'nome_razao' => $request->nome,
      'email' => $request->email,
      'telefone' => $request->telefone,
      'cpf_cnpj' => $request->cpf_cnpj,
    ]);

    // atializa dados de endereço da pessoa se inscrição individual
    if(!$empresa) {
      Endereco::updateOrCreate([
        'uid' => $request->id_endereco ?? null,
      ],[
        'cep' => $request->cep,
        'uf' => $request->uf,
        'endereco' => $request->endereco,
        'complemento' => $request->complemento,
        'bairro' => $request->bairro,
        'cidade' => $request->cidade
      ]);
    }

    // adiciona inscrito ao curso
    $this->adicionaInscrito($agendacurso, $inscrito, $empresa, $associado);

    // salva dados financeiros
    $this->adicionaLancamentoFinanceiro($agendacurso, $inscrito, $empresa, $associado);

    // se o inscrito for um convidado ou inscrição avusa, limpa sessão para concluir o processo
    if($request->convidado || !$empresa) {
      session()->forget(['curso', 'empresa', 'convite']);
    }

    // redireciona para painel
    return redirect('painel');
  }

  /**
   * Adiciona empresa contratante a inscricao do curso
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function informaEmpresa(Request $request): RedirectResponse
  {
    // valida dados
    $request->validate([
      'cnpj' => ['required', 'cnpj'],
      ],[
      'cnpj.required' => 'Digite o CNPJ da empresa',
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
   * Salva informações de convites no banco e envia emails aos
   * inscritos
   *
   * @param Request $request
   * @return RedirectResponse
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
    
    if( Pessoa::find($validated['id_pessoa'])->empresas()->count() == 0 ) {
      return back()->with('error', 'Você precisa adicionar uma empresa para enviar convites');
    }

    // salva convites no banco de dados
    foreach($validated['indicacao-nome'] as $key => $nome) {

      // pula se o email não for revalidado
      if(preg_match("/^[0-9a-z]([-_.]*?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,9}$/", $validated['indicacao-email'][$key], $matches) == 0) {
        continue;
      }
      // evita envio de convite duplicado
      Convite::firstOrCreate([
        'agenda_curso_id' => $validated['id_curso'],
        'email' => $validated['indicacao-email'][$key],
      ],[
        'pessoa_id' => $validated['id_pessoa'],
        'nome' => $nome,
      ]);
    }

    return back()->with('success', 'Convites enviados com sucesso!');

  }

  /**
   * Adiciona / Edita inscrito manualmente na tela de agenda de cursos
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function salvaInscrito(Request $request): RedirectResponse
  {
    // TODO criar lógica
    return back()->with('success', 'Dados salvos com sucesso!');
  }

  /**
   * Cancela a inscrição de um participante
   *
   * @param CursoInscrito $inscrito
   * @return RedirectResponse
   */
  public function cancelaInscricao(CursoInscrito $inscrito): RedirectResponse
  {
    $inscrito->delete();
    $this->atualizaFinanceiro($inscrito);

    return back()->with('success', 'Inscrição cancelada com sucesso!');
  }

/**
 * Conclui o processo de inscrição removendo da sessão 
 * todos os dados relacionados ao curso
 * 
 * @return RedirectResponse
 */
  public function concluiInscricao(): RedirectResponse
  {
    session()->forget(['curso', 'empresa', 'convite']);
    return back()->with('success', 'Processo de inscrição concluido com sucesso!');
  }

  /**
   * Adiciona os dados de inscricao no curso
   *
   * @param AgendaCursos $agendacurso
   * @param Pessoa $inscrito
   * @param Pessoa|null $empresa
   * @param bool $associado
   * 
   * @return void
   */
  private function adicionaInscrito(AgendaCursos $agendacurso, Pessoa $inscrito, Pessoa $empresa = null, $associado): void
  {
    CursoInscrito::updateOrCreate([
      'pessoa_id' => $inscrito->id,
      'agenda_curso_id' => $agendacurso->id,
    ],[
      'empresa_id' => $empresa?->id ?? null,
      'valor' => ($associado) ? $agendacurso->investimento_associado : $agendacurso->investimento,
      'data_inscricao' => now(),
    ]);

  }

  /**
   * Adiciona lançamentos financeiros referentes a inscrição no curso
   *
   * @param AgendaCursos $agendacurso
   * @param Pessoa $inscrito
   * @param Pessoa|null $empresa
   * @param bool $associado
   * 
   * @return void
   */
  private function adicionaLancamentoFinanceiro(AgendaCursos $agendacurso, Pessoa $inscrito, Pessoa $empresa = null, $associado): void
  {
    $valor = ($associado) ? $agendacurso->investimento_associado : $agendacurso->investimento;

    // se a inscrição está associada a uma empresa
    if( $empresa ) {

      $lancamento = LancamentoFinanceiro::where('pessoa_id', $empresa->id)
      ->where('agenda_curso_id', $agendacurso->id)
      ->first();
      
      // se a empresa não possui inscritos nesse curso, cria um novo lançamento
      if(!$lancamento) {
        LancamentoFinanceiro::create([
          'pessoa_id' => $empresa->id,
          'agenda_curso_id' => $agendacurso->id,
          'historico' => 'Inscrição no curso - ' . $agendacurso->curso->descricao,
          'valor' => $valor,
          'centro_custo_id' => '3', // TREINAMENTO
          'centro_custo_id' => '3', // RECEITA PRESTAÇÃO DE SERVIÇOS
          'data_emissao' => now(),
          'status' => 'PROVISIONADO',
        ]);
      } else { // se a empresa já possui inscritos nesse curso, atualiza o valor

        $dados_empresa = CursoInscrito::where('empresa_id', $empresa->id)
        ->where('agenda_curso_id', $agendacurso->id)
        ->with('pessoa')
        ->get();

        $observacoes = '';
        foreach($dados_empresa as $dado) {
          $data = Carbon::parse($dado->data_inscricao)->format('d/m/Y H:i');
          $observacoes .= "Inscrição de {$dado->pessoa->nome_razao}, com valor de R$ {$dado->valor}, em {$data} \n";
        }

        $lancamento->update([
          'valor' => $dados_empresa->sum('valor'),
          'observacoes' => $observacoes
        ]);
      }

    } else { // se a inscrição é de pessoa física

      $lancamento = LancamentoFinanceiro::create([
        'pessoa_id' => $inscrito->id,
        'agenda_curso_id' => $agendacurso->id,
        'historico' => 'Inscrição no curso - ' . $agendacurso->curso->descricao,
        'valor' => $valor,
        'centro_custo_id' => '3', // TREINAMENTO
        'centro_custo_id' => '3', // RECEITA PRESTAÇÃO DE SERVIÇOS
        'data_emissao' => now(),
        'status' => 'PROVISIONADO',
      ]);

    }
  }

  /**
   * Attualiza lançamento financeiro removendo o valor da inscrição
   * deletada
   *
   * @param CursoInscrito $inscrito
   * @return void
   */
  private function atualizaFinanceiro(CursoInscrito $inscrito): void
  {
    $lancamento = LancamentoFinanceiro::where('pessoa_id', $inscrito->empresa_id)
      ->where('agenda_curso_id', $inscrito->agenda_curso_id)
      ->first();

    $dados_empresa = CursoInscrito::where('empresa_id', $inscrito->empresa_id)
      ->where('agenda_curso_id', $inscrito->agenda_curso_id)
      ->with('pessoa')
      ->get();

    $observacoes = '';
    foreach($dados_empresa as $dado) {
      $data = Carbon::parse($dado->data_inscricao)->format('d/m/Y H:i');
      $observacoes .= "Inscrição de {$dado->pessoa->nome_razao}, com valor de R$ {$dado->valor}, em {$data} \n";
    }

    $lancamento->update([
      'valor' => $dados_empresa->sum('valor'),
      'observacoes' => $observacoes
    ]);
  }

}
