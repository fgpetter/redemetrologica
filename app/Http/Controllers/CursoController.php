<?php

namespace App\Http\Controllers;

use App\Models\AgendaCursos;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Intervention\Image\Facades\Image;
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

      ],
      [
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
      $original_name = $request->file('folder')->getClientOriginalName();
      $file_name = pathinfo($original_name, PATHINFO_FILENAME);
      $file_name = str_replace(' ', '-', $file_name);
      $extension = $request->file('folder')->getClientOriginalExtension();

      // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
      if ($extension == 'jpg' || $extension == 'png' || $extension == 'jpeg') {
        $image = $request->file('thumb');
        $img = Image::make($image);
        $img->encode('jpg', 75);
  
        $file_name = $file_name . '_' . time() . '.jpg';
  
        $img->save(public_path('curso-thumb/' . $file_name));
      } else {
        
        $file_name = $file_name . '_' . time() . '.' . $extension;
        $request->file('folder')->move(public_path('curso-folder'), $file_name);
      }

      $validated['folder'] = $file_name;
    }


    if ($request->hasFile('thumb')) {
      $original_name = $request->file('thumb')->getClientOriginalName();
      $file_name = pathinfo($original_name, PATHINFO_FILENAME);
      $file_name = str_replace(' ', '-', $file_name);

      $image = $request->file('thumb');
      $img = Image::make($image);
      $img->encode('jpg', 75);

      $file_name = $file_name . '_' . time() . '.jpg';

      $img->save(public_path('curso-thumb/' . $file_name));

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

      ],
      [
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
      $originName = $request->file('folder')->getClientOriginalName();
      $fileName = pathinfo($originName, PATHINFO_FILENAME);
      $fileName = str_replace(' ', '-', $fileName);
      $extension = $request->file('folder')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('folder')->move(public_path('curso-folder'), $fileName);

      // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
      if ($extension == 'jpg' || $extension == 'png' || $extension == 'jpeg') {
        $img = Image::make(public_path('curso-folder/' . $fileName));
        if ($img->height() > 750) {
          $img->resize(null, 750, function ($constraint) {
            $constraint->aspectRatio();
          });
        }
        $img->encode('jpg', 75);
        $img->save(public_path('curso-folder/' . $fileName));
      }

      $validated['folder'] = $fileName;
    }

    if ($request->hasFile('thumb')) {
      $originName = $request->file('thumb')->getClientOriginalName();
      $fileName = pathinfo($originName, PATHINFO_FILENAME);
      $fileName = str_replace(' ', '-', $fileName);
      $extension = $request->file('thumb')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('thumb')->move(public_path('curso-thumb'), $fileName);

      // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
      if ($extension == 'jpg' || $extension == 'png' || $extension == 'jpeg') {
        $img = Image::make(public_path('curso-thumb/' . $fileName));
        if ($img->height() > 750) {
          $img->resize(null, 750, function ($constraint) {
            $constraint->aspectRatio();
          });
        }
        $img->encode('jpg', 75);
        $img->save(public_path('curso-thumb/' . $fileName));
      }

      $validated['thumb'] = $fileName;
    }


    $curso->update($validated);

    return redirect()->back()
      ->with('success', 'Curso atualizado com sucesso');
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
