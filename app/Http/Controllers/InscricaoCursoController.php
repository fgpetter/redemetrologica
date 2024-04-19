<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\AgendaCursos;
use App\Models\CentroCusto;
use Illuminate\Http\Request;
use App\Models\CursoInscrito;
use App\Models\LancamentoFinanceiro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class InscricaoCursoController extends Controller
{
    /**
     * Verifica o link de inscrição e faz o roteamento da inscrição 
     * conforme os critérios
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function cursoInscricao(Request $request): RedirectResponse
    {
        // limpa a sessao
        session()->forget(['curso', 'empresa', 'convite']);
        
        if($request->invite && $request->invite == 1) {
            session()->put('convite', true);
        }

        // verificar se o curso existe e se tem inscrições abertas
        // salva informações na sessão
        $agendacurso = AgendaCursos::where('uid', $request->target)->where('inscricoes', 1)->first() ?? abort(404);
        session()->put('curso', $agendacurso);
        
        // verifica se é um convite e salva informações na sessão
        if($request->referer) {
            $empresa = Pessoa::where('uid', $request->referer)->where('tipo_pessoa', 'PJ')->first() ?? null;
            ($empresa) ? session()->put('empresa', $empresa) : abort(404);
        }

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
        $request->validate([
            'nome' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'telefone' => ['required', 'celular_com_ddd'],
            'cpf_cnpj' => ['required', 'cpf'],
            'id_empresa' => ['nullable', 'exists:pessoas,id'],
            ],[
            'nome.required' => 'Preencha o campo nome',
            'nome.string' => 'O dado enviado não é válido',
            'nome.max' => 'O dado enviado ultrapassa o limite de 190 caracteres',
            'email.required' => 'Preencha o campo email',
            'email.email' => 'O dado enviado não é um email válido',
            'email.max' => 'O dado enviado ultrapassa o limite de 190 caracteres',
            'telefone.required' => 'Preencha o campo telefone',
            'telefone.celular_com_ddd' => 'O dado enviado não é um telefone válido',
            'cpf_cnpj.required' => 'Preencha o campo CPF',
            'cpf_cnpj.cpf' => 'O dado enviado não é um CPF válido',
        ]);

        $agendacurso = AgendaCursos::where('id', $request->id_curso)->first();
        
        // verifica se a empresa já tem cadastro no curso
        if($request->id_empresa){
            
            $associado = Pessoa::where('id', $request->id_empresa)->where('associado', 1)->exists();

            CursoInscrito::firstOrCreate([
                'pessoa_id' => $request->id_empresa,
                'agenda_curso_id' => $request->id_curso
            ],[
                "data_inscricao" => now()
            ]);

        } else {
            $associado = Pessoa::where('id', $request->id_pessoa)->where('associado', 1)->exists();
        }

        // atualiza dados da pessoa
        $pessoa = Pessoa::where('id', $request->id_pessoa)->first();
        $pessoa->update(
            [
                'nome_razao' => $request->nome,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'cpf_cnpj' => $request->cpf_cnpj,
            ]
        );
        $pessoa->empresas()->sync($request->id_empresa);

        // adiciona pessoa a cursos_inscritos
        $this->adicionaInscrito([
            'pessoa_id' => $request->id_pessoa,
            'empresa_id' => $request->id_empresa ?? $id_empresa ?? null,
            'agenda_curso_id' => $request->id_curso,
            'valor' => ($associado) ? $agendacurso->investimento_associado : $agendacurso->investimento,
            'data_inscricao' => now()
        ]);

        // se enviados convites, envia email de convite

        // remove dados da sessão
        session()->forget(['curso', 'empresa', 'convite']);

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
            'cnpj' => ['nullable', 'cnpj'],
            ],[
            'cnpj.cnpj' => 'O dado enviado não é um CNPJ válido',
        ]);

        $empresa = Pessoa::where('cpf_cnpj', preg_replace('/[^0-9]/', '', $request->cnpj))->where('tipo_pessoa', 'PJ')->first() ?? null;
        
        if(!$empresa) {
            return back()->with('error', 'Empresa não encontrada');
        }

        session()->put('empresa', $empresa);

        return back()->with('success', 'Empresa adicionada com sucesso!');
    }


    /**
     * Adiciona / Edita inscrito manualmente na tela de agenda de cursos
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

        $this->adicionaInscrito([]);

        return back()->with('success', 'Dados salvos com sucesso!');
    }

    private function adicionaInscrito($dado_inscrito){

        // adiciona os dados de inscricao
        CursoInscrito::updateOrCreate([
            'uid' => $dado_inscrito->inscrito_uid ?? config('hashing.uid'),
        ],[
            'pessoa_id' => $dado_inscrito['pessoa_id'],
            'empresa_id' => $dado_inscrito['empresa_id'] ?? null,
            'agenda_curso_id' => $dado_inscrito['agenda_curso_id'],
            'valor' => $dado_inscrito['valor'],
            'data_inscricao' => now(),
            'data_confirmacao' => $dado_inscrito['data_confirmacao'] ?? null,
            'certificado_emitido' => $dado_inscrito['certificado_emitido'] ?? null,
            'resposta_pesquisa' => $dado_inscrito['resposta_pesquisa'] ?? null,
        ]);

        // adiciona lancamento financeiro por empresa
        if( isset($dado_inscrito['empresa_id']) ) {
            $lancamento = LancamentoFinanceiro::where('pessoa_id', $dado_inscrito['empresa_id'])
                ->where('agenda_curso_id', $dado_inscrito['agenda_curso_id'])
                ->first();

            if(!$lancamento) {
                $lancamento = LancamentoFinanceiro::create([
                    'uid' => config('hashing.uid'),
                    'pessoa_id' => $dado_inscrito['empresa_id'],
                    'agenda_curso_id' => $dado_inscrito['agenda_curso_id'],
                    'historico' => 'Inscrição curso -' . AgendaCursos::find($dado_inscrito['agenda_curso_id'])->curso->descricao,
                    'valor' => $dado_inscrito['valor'],
                    'centro_custo_id' => CentroCusto::where('descricao', 'TREINAMENTO')->first()->id,
                    'data_emissao' => now(),
                    'status' => 'PROVISIONADO',
                    'observacoes' => 'Inscrição de participante - ' . Pessoa::find($dado_inscrito['pessoa_id'])->nome_razao ." - R$". $dado_inscrito['valor'],
                ]);
            } else {
                $lancamento->update([
                    'valor' => $lancamento->valor + $dado_inscrito['valor'],
                    'observacoes' => $lancamento->observacoes . "\n" . 'Inscrição de participante - ' . Pessoa::find($dado_inscrito['pessoa_id'])->nome_razao . " - R$". $dado_inscrito['valor'],
                ]);
            }

        }
    }
}
