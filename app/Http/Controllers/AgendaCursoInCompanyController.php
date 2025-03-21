<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Pessoa;
use App\Models\Instrutor;
use App\Models\AgendaCursos;
use App\Models\MaterialPadrao;
use Illuminate\Contracts\View\View;
use App\Models\LancamentoFinanceiro;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\AgendaCursoRequest;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class AgendaCursoInCompanyController extends Controller
{

  /**
   * Gera tela de lista de cursos agendados
   * 
   * @return View
   */
  public function index(Request $request)
{
    $sortDirection = $request->input('order', 'asc'); // direção da ordenação
    $sortField = $request->input('orderBy', 'data_inicio'); // campo de ordenação
    $searchTerm = $request->input('buscanome'); // termo de busca

    $agendacursos = AgendaCursos::with('curso', 'inscritos')
        ->where('tipo_agendamento', 'IN-COMPANY')
        ->when($searchTerm, function ($query) use ($searchTerm) {
            $query->whereHas('curso', function ($query) use ($searchTerm) {
                $query->where('descricao', 'LIKE', "%{$searchTerm}%");
            });
        })
        ->when($sortField, function ($query) use ($sortDirection, $sortField) {
            if ($sortField === 'curso') {
                $query->orderBy(
                    Curso::select('descricao')
                        ->whereColumn('cursos.id', 'agenda_cursos.curso_id'),
                    $sortDirection
                );
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        })
        ->paginate(10);

    return view('painel.agendamento-cursos.index', [
        'agendacursos' => $agendacursos,
        'tipoagenda' => 'IN-COMPANY'
    ]);
}

  /**
   * Tela de cadastro e edição de agenda de cursos in-company
   * 
   * @param AgendaCursos $agendamento_curso
   * @return View
   */
  public function insert(AgendaCursos $agendacurso): View
  {
    $agendacurso->load('instrutor.pessoa', 'curso.materiais', 'inscritos');
    $pessoas = Pessoa::select('id','uid', 'cpf_cnpj', 'tipo_pessoa' , 'nome_razao')->get();

    $data = [
      'instrutores' => Instrutor::select('id','uid', 'pessoa_id')->with('pessoa')->whereNot('id', $agendacurso->instrutor_id)->get(),
      'instrutor_atual' => $agendacurso->instrutor()->with('pessoa')->withTrashed()->first(),
      'cursos' => Curso::select('id', 'descricao')->whereNot('id', $agendacurso->curso_id)->get(),
      'curso_atual' => $agendacurso->curso()->withTrashed()->first(),
      'empresas' => $pessoas->where('tipo_pessoa', 'PJ'),
      'pessoas' => $pessoas->where('tipo_pessoa', 'PF'),
      'inscritos' => $agendacurso->inscritos()->with('pessoa')->get(),
      'despesas' => $agendacurso->despesas()->with('materialPadrao:id,descricao')->get(),
      'materiaispadrao' => MaterialPadrao::select('id', 'descricao')->whereiN('tipo', ['CURSOS', 'AMBOS'])->get(),
      'agendacurso' => $agendacurso,
      'tipoagenda' => 'IN-COMPANY'
    ];

    return view('painel.agendamento-cursos.insert', $data);
  }

  /**
   * Adiciona um agendamento de curso in-company
   * 
   * @param AgendaCursoRequest $request
   * @return RedirectResponse
   */
  public function create(AgendaCursoRequest $request): RedirectResponse
  {
    $validated = $request->validated();
    $validated['site'] = 0;
    $validated['inscricoes'] = 0;
    $validated['destaque'] = 0;

    $agendacurso = AgendaCursos::create($validated);

    if($request->material){
      $agendacurso->cursoMateriais()->sync($request->material);
      unset($validated['material']);
    }

    if (!$agendacurso) {
      return back()->with('agendamento-error', 'Houve um erro, tente novamente');
    }

    if($request->status_proposta == 'APROVADA' && $request->valor_orcamento >0){
      LancamentoFinanceiro::updateOrCreate([
        'pessoa_id' => $agendacurso->empresa_id,
        'agenda_curso_id' => $agendacurso->id
      ],[
        'historico' => 'Curso In Company - ' . $agendacurso->curso->descricao,
        'valor' => formataMoeda($request->valor_orcamento),
        'centro_custo_id' => '3', // TREINAMENTO
        'plano_conta_id' => '3', // RECEITA PRESTAÇÃO DE SERVIÇOS
        'data_emissao' => now(),
        'status' => 'PROVISIONADO',
      ]);
    }

    return back()->with('success', 'Agendamento in-company cadastrado com sucesso');
  }

  /**
   * Atualiza dados de agenda de cursos in-company
   * 
   * @param AgendaCursos $agendacurso
   * @param AgendaCursoRequest $request
   * @return RedirectResponse
   */
  public function update(AgendaCursos $agendacurso, AgendaCursoRequest $request): RedirectResponse
  {
    $validated = $request->validated();
    $validated['tipo_agendamento'] = 'IN-COMPANY';
    $validated['site'] = 0;
    $validated['inscricoes'] = 0;
    $validated['destaque'] = 0;

    if($request->material){
      $agendacurso->cursoMateriais()->sync($request->material);
      unset($validated['material']);
    }

    $agendacurso->update($validated);

    if($request->status_proposta == 'APROVADA' && $request->valor_orcamento >0){
      LancamentoFinanceiro::updateOrCreate([
        'pessoa_id' => $agendacurso->empresa_id,
        'agenda_curso_id' => $agendacurso->id
      ],[
        'historico' => 'Curso In Company - ' . $agendacurso->curso->descricao,
        'valor' => formataMoeda($request->valor_orcamento),
        'centro_custo_id' => '3', // TREINAMENTO
        'plano_conta_id' => '3', // RECEITA PRESTAÇÃO DE SERVIÇOS
        'data_emissao' => now(),
        'status' => 'PROVISIONADO',
      ]);
    }

    return back()->with('success', 'Agendamento in-company atualizado com sucesso');
  }
} 