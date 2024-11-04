<?php

namespace App\Http\Controllers;

use App\Models\{AgendaInterlab, Pessoa, CentroCusto, Interlab, InterlabInscrito, LancamentoFinanceiro};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\Validator;

class InscricaoInterlabController extends Controller
{
    /**
     * Faz roteamento da inscrição salvando dados do link na sessão
     * e redirecionando para o painel
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function interlabInscricao(Request $request): RedirectResponse
    {

        // limpa a sessao
        session()->forget(['interlab','curso', 'empresa', 'convite']);

        if($request->invite && $request->invite == 1) {
          session()->put('convite', true);
        }

        // verificar se o interlab existe e se tem inscrições abertas
        $agenda_interlab = AgendaInterlab::where('uid', $request->target)->where('inscricao', 1)->first() ?? abort(404);

        // salva informações na sessão
        session()->put('interlab', $agenda_interlab);
        
        // verifica se é um convite e salva informações na sessão
        if($request->referer) {
            $empresa = Pessoa::where('uid', $request->referer)->where('tipo_pessoa', 'PJ')->first() ?? null;
            ($empresa) ? session()->put('empresa', $empresa) : abort(404);
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

        // verifica se a empresa já tem cadastro no interlab
        if($request->id_empresa){

            InterlabInscrito::firstOrCreate([
                'pessoa_id' => $request->id_empresa,
                'agenda_interlab_id' => $request->id_interlab
            ],[
                "data_inscricao" => now()
            ]);

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

        // adiciona pessoa a interlabs_inscritos
        $this->adicionaInscrito([
            'pessoa_id' => $request->id_pessoa,
            'empresa_id' => $request->id_empresa ?? $id_empresa ?? null,
            'agenda_interlab_id' => $request->id_interlab,
            'data_inscricao' => now()
        ]);

        // se enviados convites, envia email de convite

        // remove dados da sessão
        session()->forget(['interlab', 'empresa', 'convite']);

        // redireciona para painel
        return redirect('painel');
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

        session()->put('empresa', $empresa);

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
    public function cancelaInscricao(InterlabInscrito $inscrito){
        $inscrito->delete();
        return back()->with('success', 'Inscrição cancelada com sucesso!');
    }

    private function adicionaInscrito($dado_inscrito){

        // adiciona os dados de inscricao
        InterlabInscrito::updateOrCreate([
            'uid' => $dado_inscrito['uid'] ?? config('hashing.uid'),
        ],[
            'pessoa_id' => $dado_inscrito['pessoa_id'],
            'empresa_id' => $dado_inscrito['empresa_id'] ?? null,
            'agenda_interlab_id' => $dado_inscrito['agenda_interlab_id'],
            'data_inscricao' => $dado_inscrito['data_inscricao'] ?? now(),
            'certificado_emitido' => $dado_inscrito['certificado_emitido'] ?? null,
            'resposta_pesquisa' => $dado_inscrito['resposta_pesquisa'] ?? null,
        ]);

        // adiciona lancamento financeiro por empresa
        // TODO: revisar o processo financeiro para interlab
    }

    private function formataMoeda($valor): ?string
    {
        if ($valor) {
            if (str_contains($valor, '.') && str_contains($valor, ',')) {
                return str_replace(',', '.', str_replace('.', '', $valor));
            }

            if (str_contains($valor, '.') && !str_contains($valor, ',')) {
                return $valor;
            }

            if (str_contains($valor, ',') && !str_contains($valor, '.')) {
                return str_replace(',', '.', $valor);
            }
        } else {
            return null;
        }
    }
  
}
