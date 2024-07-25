<?php

namespace App\Http\Controllers;

use App\Models\Interlab;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File as FileFacade;


class InterlabController extends Controller
{
  /**
   * Gera pagina de listagem de intelabs
   *
   * @return View
   **/
  public function index(): View
  {
    $interlabs = Interlab::paginate(15);
    return view('painel.interlabs.index', ['interlabs' => $interlabs]);
  }

  /**
   * Tela de edição de interlab
   *
   * @param Interlab $interlab
   * @return View
   **/
  public function insert(Interlab $interlab): View
  {
    return view('painel.interlabs.insert', ['interlab' => $interlab]);
  }


  /**
   * Adiciona interlab na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $validated = $request->validate(
      [
        'nome' => ['required','string', 'max:190'],
        'descricao' => ['nullable', 'string'],
        'tipo' => ['nullable', 'string', 'in:BILATERAL,INTERLABORATORIAL'],
        'thumb' => ['nullable', File::types(['jpg', 'jpeg', 'png'])->max(2 * 1024)],
        'observacoes' => ['nullable', 'string'],
      ],
      [
        'nome.required' => 'Preencha o campo Nome',
        'nome.string' => 'O campo aceita somente texto.',
        'nome.max' => 'O campo aceita no maximo 190 caracteres.',
        'descricao.string' => 'O campo aceita somente texto.',
        'tipo.in' => 'A opção selecionada é inválida',
        'observacoes' => 'O campo aceita somente texto.',
        'thumb.mimes' => 'Apenas arquivos JPG,PNG são permitidos.',
        'thumb.max' => 'O arquivo é muito grande, dimiua o arquivo usando www.tinyjpg.com.',
      ]
    );

    if ($request->hasFile('thumb')) {
      $original_name = $request->file('thumb')->getClientOriginalName();
      $file_name = pathinfo($original_name, PATHINFO_FILENAME);
      $file_name = str_replace(' ', '-', $file_name);

      $image = $request->file('thumb');
      $img = Image::make($image);

      if ($img->width() > 300) {
        $img->resize(300, null, function ($constraint) {
          $constraint->aspectRatio();
        });
      }

      $img->encode('jpg', 75);

      $file_name = $file_name . '_' . time() . '.jpg';

      $img->save(public_path('interlab-thumb/' . $file_name));

      $validated['thumb'] = $file_name;
    }

    $interlab = Interlab::create($validated);

    if (!$interlab) {
      return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('interlab-index')->with('success', 'Interlab cadastrado com sucesso');
  }

  /**
   * Altera interlab
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function update(Request $request, Interlab $interlab): RedirectResponse
  {
    $validated = $request->validate(
      [
        'nome' => ['required','string', 'max:190'],
        'descricao' => ['nullable', 'string'],
        'tipo' => ['nullable', 'string', 'in:BILATERAL,INTERLABORATORIAL'],
        'thumb' => ['nullable', File::types(['jpg', 'jpeg', 'png'])->max(2 * 1024)],
        'observacoes' => ['nullable', 'string'],
      ],
      [
        'nome.required' => 'Preencha o campo Nome',
        'nome.string' => 'O campo aceita somente texto.',
        'nome.max' => 'O campo aceita no maximo 190 caracteres.',
        'descricao.string' => 'O campo aceita somente texto.',
        'tipo.in' => 'A opção selecionada é inválida',
        'observacoes' => 'O campo aceita somente texto.',
        'thumb.mimes' => 'Apenas arquivos JPG,PNG são permitidos.',
        'thumb.max' => 'O arquivo é muito grande, dimiua o arquivo usando www.tinyjpg.com.',
      ]
    );

    if ($request->hasFile('thumb')) {
      $original_name = $request->file('thumb')->getClientOriginalName();
      $file_name = pathinfo($original_name, PATHINFO_FILENAME);
      $file_name = str_replace(' ', '-', $file_name);

      $image = $request->file('thumb');
      $img = Image::make($image);

      if ($img->width() > 300) {
        $img->resize(300, null, function ($constraint) {
          $constraint->aspectRatio();
        });
      }

      $img->encode('jpg', 75);

      $file_name = $file_name . '_' . time() . '.jpg';

      $img->save(public_path('interlab-thumb/' . $file_name));

      $validated['thumb'] = $file_name;
    } else {
      unset($validated['thumb']);
    }

    $interlab->update($validated);

    return redirect()->route('interlab-index')->with('success', 'Interlab atualizado com sucesso');
  }

  /**
   * Remove interlab
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function delete(Interlab $interlab): RedirectResponse
  {
    if (FileFacade::exists(public_path('interlab-thumb/' . $interlab->thumb))) {
      FileFacade::delete(public_path('interlab-thumb/' . $interlab->thumb));
    }

    // $tem_interlabs_agendados = AgendaCursos::where('interlab_id', $interlab->id)->first();
    // (!$tem_interlabs_agendados) ? $interlab->forceDelete() : $interlab->delete();

    $interlab->forceDelete();

    return redirect()->route('interlab-index')->with('warning', 'Interlab removido');
  }

  /**
   * Remove arquivo de thumb
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function thumbDelete(Interlab $interlab): RedirectResponse
  {

    if (FileFacade::exists(public_path('interlab-thumb/' . $interlab->thumb))) {
      FileFacade::delete(public_path('interlab-thumb/' . $interlab->thumb));
    }

    $interlab->update(['thumb' => null]);

    return redirect()->back()->with('success', 'Arquivo de thumb removido');
  }

}
