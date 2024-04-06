<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

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
     * Cadastra cliente no curso
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

            CursoInscrito::updateOrCreate([
                'pessoa_id' => $request->id_empresa,
                'agenda_curso_id' => $request->id_curso
            ],
            [
                'uid' => config('hashing.uid'),
                'pessoa_id' => $request->id_empresa,
                'agenda_curso_id' => $request->id_curso,
                'data_inscricao' => now()
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
        CursoInscrito::create([
            'uid' => config('hashing.uid'),
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

    public function informaEmpresa(Request $request): RedirectResponse
    {
        // valida dados
        $request->validate([
            'cnpj' => ['nullable', 'cnpj'],
            ],[
            'cnpj.required' => 'Preencha o campo CNPJ',
            'cnpj.cnpj' => 'O dado enviado não é um CNPJ válido',
        ]);

        $empresa = Pessoa::where('cpf_cnpj', preg_replace('/[^0-9]/', '', $request->cnpj))->where('tipo_pessoa', 'PJ')->first() ?? null;
        
        if(!$empresa) {
            return back()->with('error', 'Empresa não encontrada');
        }

        session()->put('empresa', $empresa);

        return back()->with('success', 'Empresa adicionada com sucesso!');
    }
}
