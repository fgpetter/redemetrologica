<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\AreaAtuacao;
use App\Models\Laboratorio;
use Illuminate\Http\Request;
use App\Models\LaboratorioInterno;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Http\RedirectResponse;

class LaboratorioController extends Controller
{
  /**
   * Gera pagina de listagem de laboratorios
   *
   * @return View
   **/
  public function index(): View
  {
    $laboratorios = Laboratorio::with('pessoa')->paginate(10);
    $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj')
      ->where('tipo_pessoa', 'PJ')
      ->whereNotIn('id', function ($query) {
        $query->select('pessoa_id')->from('laboratorios');
      })
      ->get();

    return view('painel.laboratorios.index', ['laboratorios' => $laboratorios, 'pessoas' => $pessoas]);
  }

  /**
   * Adiciona um laboratorio
   *
   * @param Request $request
   * @return RedirectResponse
   **/
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

    // cria um laboratorio vinculado a pessoa
    $laboratorio = Laboratorio::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $pessoa->id,
    ]);

    if (!$laboratorio) {
      return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('laboratorio-insert', $laboratorio->uid)
      ->with('success', 'Laboratório cadastrado com sucesso');
  }

  /**
   * Tela de edição de laboratorio
   *
   * @param Laboratorio $laboratorio
   * @return View
   **/
  public function insert(Laboratorio $laboratorio): View
  {
    $areasatuacao = AreaAtuacao::all();
    return view('painel.laboratorios.insert', ['laboratorio' => $laboratorio, 'areasatuacao' => $areasatuacao]);
  }

  /**
   * Edita dados de laboratorio
   *
   * @param Request $request
   * @param Laboratorio $laboratorio
   * @return RedirectResponse
   **/
  public function update(Request $request, Laboratorio $laboratorio): RedirectResponse
  {
    $validated = $request->validate(
      [
        'nome_laboratorio' => ['nullable', 'string'],
        'laboratorio_associado' => ['numeric', 'in:1,0'],
        'telefone' => ['nullable', 'string'],
        'email' => ['nullable', 'email'],
        'responsavel_tecnico' => ['nullable', 'string'],
        'contato' => ['nullable', 'string'],
        'cod_laboratorio' => ['nullable', 'numeric'],
      ],
      [
        'nome_laboratorio.string' => 'O dado enviado não é válido',
        'laboratorio_associado.in' => 'Selecione uma opção',
        'laboratorio_associado.numeric' => 'Selecione uma opção',
        'telefone.string' => 'O dado enviado não é válido',
        'email.email' => 'O dado enviado não é válido',
        'responsavel_tecnico.string' => 'O dado enviado não é válido',
        'contato.string' => 'O dado enviado não é válido',
        'cod_laboratorio.numeric' => 'Código inválido',
      ]
    );

    $laboratorio->update($validated);

    return redirect()->back()->with('success', 'Laboratório atualizado com sucesso');
  }

  /**
   * Remove laboratorio
   *
   * @param Laboratorio $laboratorio
   * @return RedirectResponse
   **/
  public function delete(Laboratorio $laboratorio): RedirectResponse
  {
    $laboratorio->delete();
    return redirect()->route('laboratorio-index')->with('warning', 'Laboratório removido');
  }

  /**
   * Adiciona um laboratorio interno
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function saveInterno(LaboratorioInterno $laboratorio_interno, Request $request): RedirectResponse
  {
    $validated = $request->validate(
      [
        'laboratorio_id' => ['required', 'numeric', 'exists:laboratorios,id'],
        'area_atuacao_id' => ['required', 'numeric', 'exists:areas_atuacao,id'],
        'nome' => ['nullable', 'string'],
        'cod_labinterno' => ['nullable', 'string'],
        'telefone' => ['nullable', 'string'],
        'email' => ['nullable', 'email'],
        'responsavel_tecnico' => ['nullable', 'string'],
        'reconhecido' => ['required', 'numeric', 'in:1,0'],
        'sebrae' => ['required', 'numeric', 'in:1,0'],
        'site' => ['required', 'numeric', 'in:1,0'],
        'certificado' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:2048'],
      ],
      [
        'laboratorio_id.exists' => 'Selecione um laboratório',
        'area_atuacao_id.exists' => 'Selecione uma área válida',
        'nome.string' => 'O dado enviado não é válido',
        'cod_labinterno.string' => 'O dado enviado não é válido',
        'telefone.string' => 'O dado enviado não é válido',
        'email.email' => 'O dado enviado não é válido',
        'responsavel_tecnico.string' => 'O dado enviado não é válido',
        'reconhecido.in' => 'Selecione uma opção',
        'sebrae.in' => 'Selecione uma opção',
        'site.in' => 'Selecione uma opção',
        'certificado.file' => 'Arquivo inválido',
        'certificado.mimes' => 'O arquivo enviado não é do tipo PDF, DOC, DOCX',
        'certificado.max' => 'O arquivo é muito grande, tamanho máximo 2MB',
      ]
    );

    $data = [
      'laboratorio_id' => $request->laboratorio_id,
      'area_atuacao_id' => $validated['area_atuacao_id'],
      'nome' => $validated['nome'],
      'cod_labinterno' => $validated['cod_labinterno'],
      'telefone' => $validated['telefone'],
      'email' => $validated['email'],
      'responsavel_tecnico' => $validated['responsavel_tecnico'],
      'reconhecido' => $validated['reconhecido'],
      'sebrae' => $validated['sebrae'],
      'site' => $validated['site'],
    ];

    if ($request->hasFile('certificado')) { //certificado
      $originName = $request->file('certificado')->getClientOriginalName();
      $fileName = pathinfo($originName, PATHINFO_FILENAME);
      $fileName = str_replace(' ', '-', $fileName);
      $extension = $request->file('certificado')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('certificado')->move(public_path('certificados-lab'), $fileName);

      $data['certificado'] = $fileName;
    }

    if ($laboratorio_interno->exists) {

      if ($request->hasFile('certificado')) {
        File::delete(public_path('certificados-lab/' . $laboratorio_interno->certificado));
      }

      $laboratorio_interno->update($data);
    } else {
      $created = LaboratorioInterno::create($data);
      if (!$created) {
        return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
      }
    }

    return redirect()->back()->with('success', 'Laboratório cadastrado com sucesso');
  }

  /**
   * Remove laboratorio
   *
   * @param LaboratorioInterno $laboratorio_interno
   * @return RedirectResponse
   **/
  public function deleteInterno(LaboratorioInterno $laboratorio_interno): RedirectResponse
  {
    $laboratorio_interno->delete();
    return redirect()->back()->with('warning', 'Laboratório removido');
  }

  /**
   * Apresenta a tela de download no site
   *
   * @param Request $request description
   * @return View
   */
  public function siteIndex(Request $request): View
  {
    $categoria = preg_replace('/[^A-Za-z0-9\-]/', '', $request->input('categoria'));
    $titulo = preg_replace('/[^A-Za-z0-9\-]/', '', $request->input('descricao'));

    $laboratorios = LaboratorioInterno::select('certificado', 'laboratorio_id', 'area_atuacao_id')
      ->with('area:id,descricao', 'laboratorio:id,pessoa_id,nome_laboratorio')
      ->where('site', 1)
      ->where('reconhecido', 1)
      ->get();

    return view('site.pages.laboratorios-reconhecidos', ['laboratorios' => $laboratorios]);
  }
}
