<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Curso;
use App\Models\AgendaCursos;
use Illuminate\Http\Request;
use App\Models\CursoMaterial;
use App\Actions\FileUploadAction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\File as FileFacade;

class CursoController extends Controller
{
  /**
   * Gera pagina de listagem de cursos
   *
   * @return View
   **/
  public function index(): View
  {
    $cursos = Curso::orderBy('id', 'desc')->paginate(15);
    return view('painel.cursos.index', ['cursos' => $cursos]);
  }

  /**
   * Adiciona curso na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $validated = $request->validate(
      [
        'descricao' => ['nullable', 'string', 'max:190'],
        'tipo_curso' => ['nullable', 'string', 'in:OFICIAL,SUPLENTE,OUTROS'],
        'carga_horaria' => ['nullable', 'numeric'],
        'objetivo' => ['nullable', 'string'],
        'publico_alvo' => ['nullable', 'string'],
        'pre_requisitos' => ['nullable', 'string'],
        'exemplos_praticos' => ['nullable', 'string'],
        'referencias_utilizadas' => ['nullable', 'string'],
        'conteudo_programatico' => ['nullable', 'string'],
        'observacoes_internas' => ['nullable', 'string'],
        'folder' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'pdf'])->max(2 * 1024)],
        'thumb' => ['nullable', File::types(['jpg', 'jpeg', 'png'])->max(2 * 1024)]

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
        'folder.mimes' => 'Apenas arquivos JPG,PNG e PDF são permitidos.',
        'folder.max' => 'O arquivo é muito grande, dimiua o arquivo usando www.ilovepdf.com/pt/comprimir_pdf ou www.tinyjpg.com.',
        'thumb.mimes' => 'Apenas arquivos JPG,PNG são permitidos.',
        'thumb.max' => 'O arquivo é muito grande, dimiua o arquivo usando www.tinyjpg.com.',
      ]
    );
    $validated['uid'] = config('hashing.uid');

    if ($request->hasFile('folder')) {
      $file_name = FileUploadAction::handle($request, 'folder', 'curso-folder');
      $validated['folder'] = $file_name;
    }

    if ($request->hasFile('thumb')) {
      $file_name = FileUploadAction::handle($request, 'thumb', 'curso-thumb', ['height' => 750]);
      $validated['thumb'] = $file_name;
    }

    $curso = Curso::create($validated);

    if (!$curso) {
      return redirect()->back()
        ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('curso-index')
      ->with('success', 'Curso cadastrado com sucesso');
  }

  /**
   * Tela de edição de curso
   *
   * @param Curso $curso
   * @return View
   **/
  public function insert(Curso $curso): View
  {
    $curso->load('materiais');
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
    $validated = $request->validate(
      [
        'descricao' => ['nullable', 'string', 'max:190'],
        'tipo_curso' => ['nullable', 'string', 'in:OFICIAL,SUPLENTE,OUTROS'],
        'carga_horaria' => ['nullable', 'numeric'],
        'objetivo' => ['nullable', 'string'],
        'publico_alvo' => ['nullable', 'string'],
        'pre_requisitos' => ['nullable', 'string'],
        'exemplos_praticos' => ['nullable', 'string'],
        'referencias_utilizadas' => ['nullable', 'string'],
        'conteudo_programatico' => ['nullable', 'string'],
        'observacoes_internas' => ['nullable', 'string'],
        'folder' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'pdf'])->max(2 * 1024)],
        'thumb' => ['nullable', File::types(['jpg', 'jpeg', 'png'])->max(2 * 1024)]
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
        'folder.mimes' => 'Apenas arquivos JPG,PNG e PDF são permitidos.',
        'folder.max' => 'O arquivo é muito grande, dimiua o arquivo usando www.ilovepdf.com/pt/comprimir_pdf ou www.tinyjpg.com.',
        'thumb.mimes' => 'Apenas arquivos JPG,PNG são permitidos.',
        'thumb.max' => 'O arquivo é muito grande, dimiua o arquivo usando www.tinyjpg.com.',
      ]
    );

    if ($request->hasFile('folder')) {
      $file_name = FileUploadAction::handle($request, 'folder', 'curso-folder');
      $validated['folder'] = $file_name;
    }

    if ($request->hasFile('thumb')) {
      $file_name = FileUploadAction::handle($request, 'thumb', 'curso-thumb', ['height' => 750]);
      $validated['thumb'] = $file_name;
    }

    $curso->update($validated);

    return redirect()->back()->with('success', 'Curso atualizado com sucesso');
  }

  /**
   * Adiciona materiais ao curso
   *
   * @param Request $request
   * @param Curso $curso
   * @return RedirectResponse
   */
  public function uploadMaterial(Request $request, Curso $curso): RedirectResponse
  {
    $validator = Validator::make( $request->all(), [
        'descricao' => ['nullable', 'string', 'max:190'],
        'arquivo' => ['required', 'mimes:jpeg,png,jpg,pdf,doc,docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document','max:2048'],
      ],[
        'descricao.string' => 'O campo aceita somente texto.',
        'arquivo.mimes' => 'Apenas arquivos JPG,PNG e PDF são permitidos.',
        'arquivo.max' => 'O arquivo é muito grande, dimiua o arquivo usando www.ilovepdf.com/pt/comprimir_pdf ou www.tinyjpg.com.',
      ]
    );

    if ($validator->fails()) {

      Log::channel('validation')->info("Erro de validação", 
      [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) .'::'. __FUNCTION__ ,
          'errors' => $validator->errors() ?? null,
      ]);

      return back()
        ->with('error', 'Houve um erro a processar os dados, tente novamente')
        ->withErrors($validator)
        ->withInput();
    }


    if ($request->hasFile('arquivo')) {
      $file_name = FileUploadAction::handle($request, 'arquivo', 'curso-material');
      $file_extension = pathinfo( $file_name, PATHINFO_EXTENSION );
    }

    CursoMaterial::create([
      'curso_id' => $curso->id,
      'arquivo' => $file_name,
      'descricao' => $request->descricao
    ]);

    return back()->with('success', 'Material adicionado com sucesso');

  }

  /**
   * Remove materiais ao curso
   *
   * @param CursoMaterial $material
   * @return RedirectResponse
   **/
  public function deleteMaterial(CursoMaterial $material): RedirectResponse
  {

    if (FileFacade::exists(public_path('curso-material/' . $material->arquivo))) {
      FileFacade::delete(public_path('curso-material/' . $material->arquivo));
    }

    $material->delete();

    return redirect()->back()->with('success', 'Material removido');
  }
  
  /**
   * Remove arquivo de curso
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function folderDelete(Curso $curso): RedirectResponse
  {

    if (FileFacade::exists(public_path('curso-folder/' . $curso->folder))) {
      FileFacade::delete(public_path('curso-folder/' . $curso->folder));
    }

    $curso->update(['folder' => null]);

    return redirect()->back()->with('success', 'Folder removido');
  }

  /**
   * Remove arquivo de thumb do curso
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function thumbDelete(Curso $curso): RedirectResponse
  {

    if (FileFacade::exists(public_path('curso-thumb/' . $curso->thumb))) {
      FileFacade::delete(public_path('curso-thumb/' . $curso->thumb));
    }

    $curso->update(['thumb' => null]);

    return redirect()->back()->with('success', 'thumb removido');
  }

  /**
   * Remove curso
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function delete(Curso $curso): RedirectResponse
  {


    if (FileFacade::exists(public_path('curso-folder/' . $curso->folder))) {
      FileFacade::delete(public_path('curso-folder/' . $curso->folder));
    }
    if (FileFacade::exists(public_path('curso-thumb/' . $curso->thumb))) {
      FileFacade::delete(public_path('curso-thumb/' . $curso->thumb));
    }

    $tem_cursos_agendados = AgendaCursos::where('curso_id', $curso->id)->first();
    (!$tem_cursos_agendados) ? $curso->forceDelete() : $curso->delete();

    return redirect()->route('curso-index')->with('warning', 'Curso removido');
  }
}
