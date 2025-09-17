<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\AreaAtuacao;
use App\Models\Laboratorio;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\LaboratorioInterno;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class LaboratorioController extends Controller
{
  /**
   * Gera pagina de listagem de laboratorios
   *
   * @return View
   **/
  public function index(Request $request): View
  {
    $nome = $request->name;
    $busca_nome = $request->buscanome;

    $laboratorios = Laboratorio::with('pessoa')
      ->when($nome, function (Builder $query, $nome) {
        $query->join('pessoas', 'laboratorios.pessoa_id', '=', 'pessoas.id')
          ->orderBy('pessoas.nome_razao', $nome);
      })
      ->when($busca_nome, function (Builder $query, $busca_nome) {
        $query->whereHas('pessoa', function ($q) use ($busca_nome) {
          $q->where('nome_razao', 'LIKE', "%$busca_nome%");
        });
      })
      ->paginate(15);

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
    $request->merge(return_only_nunbers($request->only('telefone')));

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

    if ($request->hasFile('certificado')) {
      $file = $request->file('certificado');
      $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
      $fileName = $fileName . '_' . time() . '.' . $file->getClientOriginalExtension();
      $file->move(public_path('laboratorios-certificados'), $fileName);

      $data['certificado'] = $fileName;
    }

    if ($laboratorio_interno->exists) {

      if ($request->hasFile('certificado')) {
        File::delete(public_path('laboratorios-certificados/' . $laboratorio_interno->certificado));
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
   * Apresenta a tela de lista de laboratorios reconhecidos no site
   *
   * @param Request $request description
   * @return View
   */
  public function siteIndex(Request $request): View
  {
    if (!empty(request()->except('area', 'laboratorio', 'buscalaboratorio', 'page'))) {
      return abort('404');
    }

    $validator = Validator::make($request->all(), [
      "area"         => ['nullable', 'string', 'exists:areas_atuacao,uid'],
      "laboratorio"  => ['nullable', 'string'],
    ]);

    if ($validator->fails()) {
      Log::channel('validation')->info(
          "Erro de validação",
          [
              'user'    => auth()->user() ?? null,
              'request' => $request->all() ?? null,
              'uri'     => request()->fullUrl() ?? null,
              'method'  => get_class($this) . '::' . __FUNCTION__,
              'errors'  => $validator->errors() ?? null,
          ]
      );

      return abort('404');
    }

    $areas_atuacao = AreaAtuacao::select('uid', 'descricao')
      ->orderBy('descricao', 'asc')
      ->get();

    $laboratorios_internos = LaboratorioInterno::select(
      'laboratorios_internos.uid', 
      'laboratorios_internos.nome', 
      'laboratorios_internos.certificado', 
      'laboratorios_internos.laboratorio_id', 
      'laboratorios_internos.area_atuacao_id'
      )
        ->join('laboratorios', 'laboratorios_internos.laboratorio_id', '=', 'laboratorios.id')
        ->with('laboratorio.pessoa')
        ->with('area:id,descricao')
        ->when($request->area, function ($query) use ($request) {
            $query->whereHas('area', function ($q) use ($request) {
                $q->where('uid', $request->area);
            });
        })
        ->when($request->laboratorio, function ($query) use ($request) {
            $busca = $request->laboratorio;
            $query->where(function ($q) use ($busca) {
                $q->where('laboratorios_internos.nome', 'like', "%{$busca}%")
                  ->orWhereHas('laboratorio', function ($subQuery) use ($busca) {
                      $subQuery->where('nome_laboratorio', 'like', "%{$busca}%");
                  });
            });
        })
        ->where('site', 1)
        ->where('reconhecido', 1)
        ->orderBy('laboratorios.nome_laboratorio', 'asc')
        ->get();


    return view('site.pages.laboratorios-reconhecidos', [
        'laboratorios_internos' => $laboratorios_internos,
        'areas_atuacao'         => $areas_atuacao,
    ]);
  }

  /**
   * Gera pagina single de laboratorio interno com dados e certificado
   *
   * @return View
   **/
  public function showLabInterno($uid): View
  {
    $laboratorio_interno = LaboratorioInterno::where('uid', $uid)->with('area')->firstOrFail();

    return view('site.pages.slug-labinterno', ['laboratorio_interno' => $laboratorio_interno]);
  }

}
