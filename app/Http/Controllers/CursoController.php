<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Rules\PreventXSS;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File as FileFacade;


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
    $validated = $request->validate([
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
      'folder' => ['nullable', File::types(['jpg','jpeg','png','pdf'])->max(2 * 1024)] 

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
      ]
    );
    $validated['uid'] = config('hashing.uid');

    if ($request->hasFile('folder')) {
      $originName = $request->file('folder')->getClientOriginalName();
      $fileName = pathinfo($originName, PATHINFO_FILENAME);
      $fileName = str_replace(' ', '-', $fileName);
      $extension = $request->file('folder')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('folder')->move(public_path('curso-folder'), $fileName);

      // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
      if($extension == 'jpg' || $extension == 'png'){
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


    $curso = Curso::create($validated);

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
    $validated = $request->validate([
      'descricao' => ['nullable', 'string', 'max:190'],
      'tipo_curso' => ['nullable', 'string', 'in:OFICIAL,SUPLENTE,OUTROS'],
      'carga_horaria' => ['numeric'],
      'objetivo' => ['nullable', 'string'],
      'publico_alvo' => ['nullable', 'string'],
      'pre_requisitos' => ['nullable', 'string'],
      'exemplos_praticos' => ['nullable', 'string'],
      'referencias_utilizadas' => ['nullable', 'string'],
      'conteudo_programatico' => ['nullable', 'string'],
      'observacoes_internas' => ['nullable', 'string'],
      'folder' => ['nullable', File::types(['jpg','jpeg','png','pdf'])->max(2 * 1024)] 
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
      'folder.types' => 'O tipo de arquivo não é permitido.',
      'folder.max' => 'O arquivo é muito grande.',
      ]
    );

    // verifica se o arquivo foi marcado para remoção
    if ($request->deletefolder){
      $curso->update([
        'folder' => null
      ]);

      $fileToDelete = public_path('curso-folder/' . $request->deletefolder);
      if (FileFacade::exists($fileToDelete)) {
        FileFacade::delete($fileToDelete);
      }
    }

    if ($request->hasFile('folder')) {
      $originName = $request->file('folder')->getClientOriginalName();
      $fileName = pathinfo($originName, PATHINFO_FILENAME);
      $fileName = str_replace(' ', '-', $fileName);
      $extension = $request->file('folder')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('folder')->move(public_path('curso-folder'), $fileName);

      // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
      if($extension == 'jpg' || $extension == 'png'){
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


    $curso->update($validated);

    return redirect()->back()
      ->with('curso-success', 'Curso atualizado com sucesso');

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