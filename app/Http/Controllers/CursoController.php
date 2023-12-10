<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Rules\PreventXSS;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Http\RedirectResponse;


class CursoController extends Controller
{
    /**
   * Gera pagina de listagem de usuários
   *
   * @return View
   **/
  public function index(): View
  {
    $cursos = Curso::orderBy('id', 'desc')->paginate(15);
    return view('painel.cursos.index', ['cursos' => $cursos]);
  }

  /**
   * Adiciona usuários na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $data = $request->validate([
      'descricao' => ['string', 'max:190'],
      'tipo_curso' => ['string', 'in:OFICIAL,SUPLENTE,OUTROS'],
      'carga_horaria' => ['numeric'],
      'objetivo' => ['string'],
      'publico_alvo' => ['string'],
      'pre_requisitos' => ['string'],
      'exemplos_praticos' => ['string'],
      'referencias_utilizadas' => ['string'],
      'conteudo_programatico' => ['string'],
      'observacoes_internas' => ['string'],
      ],[
        'descricao.string' => 'O campo aceita somente texto.',
        'tipo_curso.in' => 'A opção selecionada é inválida',
        'objetivo.string' => 'O campo aceita somente texto.',
        'publico_alvo.string' => 'O campo aceita somente texto.',
        'pre_requisitos.string' => 'O campo aceita somente texto.',
        'exemplos_praticos.string' => 'O campo aceita somente texto.',
        'referencias_utilizadas.string' => 'O campo aceita somente texto.',
        'conteudo_programatico.string' => 'O campo aceita somente texto.',
        'observacoes_internas.string' => 'O campo aceita somente texto.',
      ]
    );

    $curso = Curso::create([
      'uid' => config('hashing.uid'),
      'descricao' => $data['descricao'],
      'tipo_curso' => $data['tipo_curso'],
      'carga_horaria' => $data['carga_horaria'],
      'objetivo' => $data['objetivo'],
      'publico_alvo' => $data['publico_alvo'],
      'pre_requisitos' => $data['pre_requisitos'],
      'exemplos_praticos' => $data['exemplos_praticos'],
      'referencias_utilizadas' => $data['referencias_utilizadas'],
      'conteudo_programatico' => $data['conteudo_programatico'],
      'observacoes_internas' => $data['observacoes_internas'],
    ]);

    if(!$curso){
      return redirect()->back()
      ->with('curso-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('curso-index')
      ->with('curso-success', 'Curso cadastrado com sucesso');
  }

  /**
   * Tela de edição de curso
   *
   * @param Curso $curso
   * @return View
   **/
  public function insert(Curso $curso): View
  {
    return view('painel.cursos.insert', ['curso' => $curso]);
  }

  /**
   * Edita dados de usuário
   *
   * @param Request $request
   * @param Curso $user
   * @return RedirectResponse
   **/
  public function update(Request $request, Curso $curso): RedirectResponse
  {
    $data = $request->validate([
      'descricao' => ['string', 'max:190'],
      'tipo_curso' => ['string', 'in:OFICIAL,SUPLENTE,OUTROS'],
      'carga_horaria' => ['numeric'],
      'objetivo' => ['string'],
      'publico_alvo' => ['string'],
      'pre_requisitos' => ['string'],
      'exemplos_praticos' => ['string'],
      'referencias_utilizadas' => ['string'],
      'conteudo_programatico' => ['string'],
      'observacoes_internas' => ['string'],
      ],[
        'descricao.string' => 'O campo aceita somente texto.',
        'tipo_curso.in' => 'A opção selecionada é inválida',
        'objetivo.string' => 'O campo aceita somente texto.',
        'publico_alvo.string' => 'O campo aceita somente texto.',
        'pre_requisitos.string' => 'O campo aceita somente texto.',
        'exemplos_praticos.string' => 'O campo aceita somente texto.',
        'referencias_utilizadas.string' => 'O campo aceita somente texto.',
        'conteudo_programatico.string' => 'O campo aceita somente texto.',
        'observacoes_internas.string' => 'O campo aceita somente texto.',
      ]
    );

    $curso->update([
      'descricao' => $data['descricao'],
      'tipo_curso' => $data['tipo_curso'],
      'carga_horaria' => $data['carga_horaria'],
      'objetivo' => $data['objetivo'],
      'publico_alvo' => $data['publico_alvo'],
      'pre_requisitos' => $data['pre_requisitos'],
      'exemplos_praticos' => $data['exemplos_praticos'],
      'referencias_utilizadas' => $data['referencias_utilizadas'],
      'conteudo_programatico' => $data['conteudo_programatico'],
      'observacoes_internas' => $data['observacoes_internas'],
    ]);

    return redirect()->route('curso-index')
      ->with('curso-success', 'Curso cadastrado com sucesso');

  }

  /**
   * Remove usuário
   *
   * @param User $user
   * @return RedirectResponse
   **/
    public function delete(Curso $curso): RedirectResponse
    {
      $curso->delete();
      return redirect()->route('curso-index')->with('curso-success', 'Curso removido');
    }


}