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
        session()->forget(['curso', 'empresa']);

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

    public function confirmaInscricao(Request $request): RedirectResponse
    {
        $agendacurso = AgendaCursos::where('id', $request->id_curso)->first();

        // verifica se a empresa já te cadastro no curso
        $empresaInscrito = CursoInscrito::where('empresa_id', $request->id_empresa)
            ->where('agenda_curso_id', $request->id_curso)->first();

        if(!$empresaInscrito) {
            CursoInscrito::create([
                'uid' => config('hashing.uid'),
                'pessoa_id' => $request->id_empresa,
                'agenda_curso_id' => $request->id_curso,
                'valor' => $agendacurso->valor,
                'data_inscricao' => now()

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

        // adiciona pessoa a cursos_inscritos
        CursoInscrito::create([
            'uid' => config('hashing.uid'),
            'pessoa_id' => $request->id_pessoa,
            'empresa_id' => $request->id_empresa ?? null,
            'agenda_curso_id' => $request->id_curso,
            'data_inscricao' => now()
        ]);

        // remove dados da sessão
        session()->forget(['curso', 'empresa']);

        // redireciona para painel
        return redirect('painel');
    }
}
