<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Pessoa;
use App\Models\Instrutor;
use App\Models\InstrutorCursoHabilitado;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\File as FileFacade;

use Illuminate\Validation\Validator;
use Illuminate\Http\RedirectResponse;

class InstrutorController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $instrutores = Instrutor::all();
    $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj')
      ->whereNotIn('id', function ($query) {
        $query->select('pessoa_id')->from('instrutores');
      })
      ->get();
    return view('painel.instrutores.index', ['instrutores' => $instrutores, 'pessoas' => $pessoas]);
  }

  /**
   * Cria um avaliador a partir de uma pessoa
   * @param Request $request
   * * @return RedirectResponse
   */
  public function create(Request $request): RedirectResponse
  {
    $request->validate(
      [
        'pessoa_uid' => ['required', 'string', 'exists:pessoas,uid'],
      ],
      [
        'pessoa_uid.required' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
        'pessoa_uid.string' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
        'pessoa_uid.exists' => 'Dados inválidos, seleciona uma pessoa e envie novamente'
      ]
    );

    $pessoa = Pessoa::select('id')->where('uid', $request->pessoa_uid)->first();



    // Cria um instrutor vinculado a pessoa
    $instrutor = Instrutor::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $pessoa->id,

    ]);

    if (!$instrutor) {
      return redirect()->back()
        ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('instrutor-insert', $instrutor->uid)
      ->with('success', 'Insrutor cadastrado com sucesso');
  }

  /**
   * Carrega tela de edição de instrutor.
   * @param Instrutor $instrutor
   * @return View
   */
  public function insert(Instrutor $instrutor): View
  {
    $data = [
      'cursos' => Curso::all(),
      'cursoshabilitados' => InstrutorCursoHabilitado::where('instrutor_id', $instrutor->id)->with('curso')->get(),
      'instrutor' => $instrutor,
    ];

    return view('painel.instrutores.insert', $data);
  }


  /**
   * Update the specified resource in storage.
   */
  public function update(Instrutor $instrutor, Request $request): RedirectResponse
  {
    $request->validate(
      [
        'cpf_cnpj' => ['required', 'string'],
        'rg_ie' => ['required', 'string'],
        'nome' => ['required', 'string'],
        'tipo_pessoa' => ['required', 'in:PF,PJ'],
        'situacao' => ['required', 'integer'],
        'curriculo' => ['file', 'mimes:doc,pdf,docx', 'max:5242880'], //5mb
        'observacoes' => ['nullable', 'string'],
      ],
      [
        'nome.required' => 'O nome não pode ficar em branco',
        'nome.string' => 'O dado enviado não é válido',
        'cpf_cnpj.required' => 'O CPF/CNPJ não pode ficar em branco',
        'cpf_cnpj.string' => 'O dado enviado não é válido',
        'rg_ie.required' => 'O RG/IE não pode ficar em branco',
        'rg_ie.string' => 'O dado enviado não é válido',
        'situacao.required' => 'Selecione uma opção válida',
        'situacao.integer' => 'Selecione uma opção válida',
        'curriculo.mimes' => 'Arquivo inválido',
        'curriculo.max' => 'O arquivo é muito grande, tamanho máximo 5mb',
        'observacoes.string' => 'O dado enviado não é válido',
        'tipo_pessoa.required' => 'O dado enviado não é válido',
        'tipo_pessoa.in' => 'O dado enviado não é válido'
      ]
    );

    if ($request->hasFile('curriculo')) {
      $originName = $request->file('curriculo')->getClientOriginalName();
      $fileName = pathinfo($originName, PATHINFO_FILENAME);
      $fileName = str_replace(' ', '-', $fileName);
      $extension = $request->file('curriculo')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('curriculo')->move(public_path('curriculos'), $fileName);
      $instrutor->update([
        'curriculo' => $fileName,
      ]);
    }


    $instrutor->update([
      'situacao' => $request->situacao,
      'observacoes' => $request->observacoes,
    ]);


    $pessoa = Pessoa::find($instrutor->pessoa_id);
    $pessoa->update([
      'nome_razao' => $request->nome,
      'tipo_pessoa' => $request->tipo_pessoa,
      'cpf_cnpj' => $request->cpf_cnpj,
      'rg_ie' => $request->rg_ie,
    ]);

    return back()->with('success', 'Insrtutor atualizado com sucesso');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function delete(Instrutor $instrutor)
  {
    if (FileFacade::exists(public_path('curriculos/' . $instrutor->curriculo))) {
      FileFacade::delete(public_path('curriculos/' . $instrutor->curriculo));
    }

    $instrutor->delete();

    return redirect()->route('instrutor-index')->with('success', 'Insrtutor removido');
  }

  public function createCursoHabilitado(Instrutor $instrutor, Request $request): RedirectResponse
  {
    $request->validate(
      [
        "curso" => ['required', 'numeric', 'exists:cursos,id'],
        "habilitado" => ['required', 'numeric', 'in:0,1'],
        "conhecimento" => ['required', 'numeric', 'in:0,1'],
        "experiencia" => ['required', 'numeric', 'in:0,1'],
        "analise_observacoes" => ['nullable', 'string'],
      ],
      [
        "curso.required" => 'O dado é inválido',
        "curso.numeric" => 'O dado é inválido',
        "curso.exists" => 'O dado é inválido',
        "habilitado.required" => 'O dado é inválido',
        "habilitado.numeric" => 'O dado é inválido',
        "habilitado.in" => 'O dado é inválido',
        "conhecimento.required" => 'O dado é inválido',
        "conhecimento.numeric" => 'O dado é inválido',
        "conhecimento.in" => 'O dado é inválido',
        "experiencia.required" => 'O dado é inválido',
        "experiencia.numeric" => 'O dado é inválido',
        "experiencia.in" => 'O dado é inválido',
        "analise_observacoes.string" => 'O dado é inválido',
      ]
    );


    $curso_habilitado = InstrutorCursoHabilitado::create([
      'uid' => config('hashing.uid'),
      'instrutor_id' => $instrutor->id,
      'curso_id' => $request->curso,
      'habilitado' => $request->habilitado,
      'conhecimento' => $request->conhecimento,
      'experiencia' => $request->experiencia,
      'observacoes' => $request->analise_observacoes,

    ]);

    if (!$curso_habilitado) {
      return back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return back()->with('success', 'Curso cadastrado com sucesso');
  }

  public function updateCursoHabilitado(InstrutorCursoHabilitado $cursohabilitado, Request $request)
  {
    $request->validate(
      [
        "habilitado" => ['required', 'numeric', 'in:0,1'],
        "conhecimento" => ['required', 'numeric', 'in:0,1'],
        "experiencia" => ['required', 'numeric', 'in:0,1'],
        "analise_observacoes" => ['nullable', 'string'],
      ],
      [
        "habilitado.required" => 'O dado é inválido',
        "habilitado.numeric" => 'O dado é inválido',
        "habilitado.in" => 'O dado é inválido',
        "conhecimento.required" => 'O dado é inválido',
        "conhecimento.numeric" => 'O dado é inválido',
        "conhecimento.in" => 'O dado é inválido',
        "experiencia.required" => 'O dado é inválido',
        "experiencia.numeric" => 'O dado é inválido',
        "experiencia.in" => 'O dado é inválido',
        "analise_observacoes.string" => 'O dado é inválido',
      ]
    );

    $cursohabilitado->update([
      'habilitado' => $request->habilitado,
      'conhecimento' => $request->conhecimento,
      'experiencia' => $request->experiencia,
      'observacoes' => $request->analise_observacoes,
    ]);

    return back()->with('success', 'Curso atualizado com sucesso');
  }

  public function deleteCursoHabilitado(InstrutorCursoHabilitado $cursohabilitado)
  {
    $cursohabilitado->delete();

    return back()->with('success', 'Curso removido com sucesso');
  }


  /**
   * Remove arquivo de curso
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function curriculodelete(Instrutor $instrutor): RedirectResponse
  {

    if (FileFacade::exists(public_path('curriculos/' . $instrutor->curriculo))) {
      FileFacade::delete(public_path('curriculos/' . $instrutor->curriculo));
    }

    $instrutor->update(['curriculo' => null]);

    return redirect()->back()->with('success', 'Currículo removido');
  }
}
