<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Pessoa;
use App\Models\Instrutor;
use Illuminate\Support\Str;
use App\Models\AgendaCursos;
use App\Models\CursoDespesa;
use Illuminate\Http\Request;
use App\Models\MaterialPadrao;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CursoInscritosExport;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\AgendaCursoRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AgendaCursoController extends Controller
{
  /**
   * Gera tela de lista de cursos agendados
   * 
   * @return View
   */
  public function index(): View
  {
    $data = [
      'agendacursos' => AgendaCursos::with('curso', 'inscritos')
        ->whereNot('tipo_agendamento', 'IN-COMPANY')
        ->orderBy('data_inicio')
        ->get(),
        'tipoagenda' => 'ABERTO'
    ];
    return view('painel.agendamento-cursos.index', $data);
  }

  /**
   * Tela de cadastro e ediçao de agenda de cursos
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
      'tipoagenda' => 'ABERTO'
    ];

    return view('painel.agendamento-cursos.insert', $data);
  }

  /**
   * Adiciona um agendamento de curso
   * 
   * @param AgendaCursoRequest $request
   * @return RedirectResponse
   */
  public function create(AgendaCursoRequest $request): RedirectResponse
  {
    $validated = $request->validated();

    $agendacurso = AgendaCursos::create($validated);
    
    if($request->material){
      $agendacurso->cursoMateriais()->sync($request->material);
      unset($validated['material']);
    } else {
      $agendacurso->cursoMateriais()->sync([]);
    }

    if (!$agendacurso) {
      return back()->with('agendamento-error', 'Houve um erro, tente novamente');
    }

    return redirect()->route('agendamento-curso-index')
      ->with('success', 'Agendamento cadastrado com sucesso');
  }

  /**
   * Atualiza dados de agenda de cursos
   * 
   * @param AgendaCursos $agendacurso
   * @param AgendaCursoRequest $request
   * @return RedirectResponse
   */
  public function update(AgendaCursos $agendacurso, AgendaCursoRequest $request): RedirectResponse
  {
    $validated = $request->validated();

    if($request->material){
      $agendacurso->cursoMateriais()->sync($request->material);
      unset($validated['material']);
    } else {
      $agendacurso->cursoMateriais()->sync([]);
    }

    $agendacurso->update($validated);

    return redirect()->route('agendamento-curso-index')
      ->with('success', 'Agendamento atualizado com sucesso');
  }

  /**
   * Remove um agendamento de curso
   * 
   * @param AgendaCursos $agendacurso
   * @return RedirectResponse
   */
  public function delete(AgendaCursos $agendacurso): RedirectResponse
  {
    $agendacurso->delete();

    return redirect()->route('agendamento-curso-index')
      ->with('warning', 'Agendamento removido com sucesso');
  }

  /**
   * Gera pagina de listagem de cursos agendados no site
   * @return View
   **/
  public function listCursosAgendados(): View
  {
    $agendacursos = AgendaCursos::select()
      ->whereIn('status', ['AGENDADO', 'CONFIRMADO'])
      ->whereNotIn('tipo_agendamento', ['IN-COMPANY'])
      ->where('site', 1)
      ->whereNotNull('data_inicio')
      ->orderBy('destaque', 'desc')
      ->orderBy('inscricoes', 'desc')
      ->orderBy('data_inicio')
      ->with('curso')
      ->get();

    return view('site.pages.cursos', ['agendacursos' => $agendacursos]);
  }

  /**
   * Gera pagina single de curso agendado
   *
   * @return View
   **/
  public function showCursoAgendado($uid): View
  {
    $agendacursos = Agendacursos::where('uid', $uid)->with('curso')->firstOrFail();

    return view('site.pages.slug-cursos', ['agendacursos' => $agendacursos]);
  }

  /**
   * Salva despesa do agendamento de curso
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function salvaDespesa(Request $request): RedirectResponse
  {
    $request->validate([
      'agenda_curso_id' => ['nullable', 'exists:agenda_cursos,id'],
      'material_padrao' => ['required', 'exists:materiais_padroes,id'],
      'quantidade' => ['required', 'regex:/[\d.,]+$/'],
      'valor' => ['required','regex:/[\d.,]+$/'],
      'total' => ['required', 'regex:/[\d.,]+$/'],
    ],[
      'agenda_curso_id.exists' => 'Houve um erro ao editar despesa. Tente novamente',
      'material_padrao.exists' => 'Selecione uma opção válida',
      'quantidade.regex' => 'O dado enviado não é valido',
      'quantidade.required' => 'Preencha o campo',
      'valor.required' => 'Preencha o campo',
      'valor.regex' => 'Não é um número válido',
      'total.required' => 'O campo não pode estar vazio',
      'total.regex' => 'Não é um número válido',
    ]);


    CursoDespesa::updateOrCreate([
      'agenda_cursos_id' => $request->agenda_curso_id,
      'material_padrao_id' => $request->material_padrao,
    ],[
      'quantidade' => $request->quantidade,
      'valor' => formataMoeda($request->valor),
      'total' => $request->total,
    ]);

    return back()->with('success', 'Despesa salva com sucesso')->withFragment('despesas');
  }

  /**
   * Remove despesa do agendamento de curso
   *
   * @param CursoDespesa $despesa
   * @return RedirectResponse
   */
  public function deleteDespesa(CursoDespesa $despesa): RedirectResponse
  {
    $despesa->delete();
    return back()->with('success', 'Despesa removida com sucesso');
  }

  /**
   * Baixa lista de presença de alunos
   *
   * @param AgendaCursos $agendacurso
   * @return BinaryFileResponse
   */
  public function exportListaPresenca(AgendaCursos $agendacurso): BinaryFileResponse
  {
    $nome = Str::slug($agendacurso->curso->descricao);
    $filename = "lista_presenca_{$nome}_{$agendacurso->data_inicio}.xlsx";
    return Excel::download(new CursoInscritosExport($agendacurso), $filename );
  }
}
