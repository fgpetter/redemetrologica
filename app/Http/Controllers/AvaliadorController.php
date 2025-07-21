<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\Endereco;
use App\Models\Avaliador;
use App\Models\AreaAtuacao;
use App\Models\Qualificacao;
use Illuminate\Http\Request;
use App\Models\AvaliadorArea;
use App\Models\StatusAvaliador;
use Illuminate\Validation\Rule;
use App\Models\AvaliacaoAvaliador;
use App\Models\AgendaAvaliacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;


class AvaliadorController extends Controller
{
  /**
   * Gera pagina de listagem de avaliadores
   *
   * @return View
   **/
  public function index(Request $request)
  {
    $order = $request->input('name', 'asc');
    $busca_nome = $request->input('buscanome');
    $busca_area = $request->input('buscaarea');
    $busca_situacao = $request->input('buscasituacao');

    $avaliadores = Avaliador::with('pessoa', 'areas.area')
      ->when($busca_nome, function ($query) use ($busca_nome) {
        $query->whereHas('pessoa', function ($query) use ($busca_nome) {
          $query->where('nome_razao', 'LIKE', "%{$busca_nome}%");
        });
      })
      ->when($busca_area, function ($query) use ($busca_area) {
        $query->whereHas('areas.area', function ($query) use ($busca_area) {
          $query->where('descricao', 'LIKE', "%{$busca_area}%");
        });
      })
      ->when($busca_situacao, function ($query) use ($busca_situacao) {
        $query->where('situacao', $busca_situacao);
      })
      ->orderBy(
        Pessoa::select('nome_razao')
          ->whereColumn('pessoas.id', 'avaliadores.pessoa_id'),
        $order
      )
      ->paginate(10)
      ->withQueryString();
        
    $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj')
      ->whereNotIn('id', function ($query) {
        $query->select('pessoa_id')->from('avaliadores');
      })
      ->get();

     return view('painel.avaliadores.index', [
        'avaliadores' => $avaliadores,
        'pessoas' => $pessoas
    ]);
  }
  /**
   * Adiciona avaliadores na base
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

    // cria um avaliador vinculado a pessoa
    $avaliador = Avaliador::create([
      'pessoa_id' => $pessoa->id,
    ]);

    if (!$avaliador) {
      return redirect()->back()
        ->with('avaliador-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('avaliador-insert', $avaliador->uid)
      ->with('success', 'Avaliador cadastrado com sucesso');
  }

  /**
   * Tela de edição de avaliador
   *
   * @param Avaliador $avaliador
   * @return View
   **/
  public function insert(Avaliador $avaliador): View
  {
    // carrega avaliações que esse avaliador realizou e suas respectivas agendas_uid
    $avaliacoes = AvaliacaoAvaliador::where('avaliador_id', $avaliador->id)->get()->map(function ($avaliacao) {
        if ($avaliacao->agenda_avaliacao_id) {
            $agenda = AgendaAvaliacao::find($avaliacao->agenda_avaliacao_id);
            $avaliacao->agenda_avaliacao_uid = $agenda ? $agenda->uid : null;
        }
        return $avaliacao;
    });

    // carrega qualificações do avaliador
    $qualificacoes = Qualificacao::where('avaliador_id', $avaliador->id)->get();
    $qualificacoes_list = [
      'atividades' => DB::table('avaliador_qualificacoes')->distinct()->get(['atividade']),
      'instrutores' => DB::table('avaliador_qualificacoes')->distinct()->get(['instrutor']),
    ];

    // carrega areas de atuação do avaliador
    $areas_atuacao = AreaAtuacao::select('id', 'uid', 'descricao')->get();

    // carrega endereço pessoal do avaliador
    $endereco_pessoal = $avaliador->pessoa->enderecos()
      ->where('pessoa_id', $avaliador->pessoa_id)
      ->whereNull('avaliador_id')
      ->first();

    // carrega endereço comercial do avaliador
    $endereco_comercial = $avaliador->pessoa->enderecos()
    ->where('avaliador_id', $avaliador->id)
    ->first(); 

    //carrega lista com empresas
    $empresas = Pessoa::select('id', 'uid', 'nome_razao', 'cpf_cnpj')
      ->where('tipo_pessoa', 'PJ')
      ->orderBy('nome_razao')
      ->get();
    
    return view(
      'painel.avaliadores.insert',
      [
        'avaliador' => $avaliador,
        'avaliacoes' => $avaliacoes,
        'qualificacoes' => $qualificacoes,
        'qualificacoes_list' => $qualificacoes_list,
        'areas_atuacao' => $areas_atuacao,
        'endereco_pessoal' => $endereco_pessoal,
        'endereco_comercial' => $endereco_comercial,
        'empresas' => $empresas,
      ]
    );
  }

  /**
   * Edita dados de avaliador
   *
   * @param Request $request
   * @param Avaliador $user
   * @return RedirectResponse
   **/
  public function update(Request $request, Avaliador $avaliador): RedirectResponse
  {
    $request->merge(return_only_nunbers($request->only('cpf_cnpj')));
   
    $request->validate(
      [
        'nome_razao' => ['required', 'string', 'max:191'],
        'cpf_cnpj' => ['required', 'string', 'max:191', 'unique:pessoas,cpf_cnpj,' . $avaliador->pessoa->id], // TODO - adicionar validação de CPF/CNPJ
        'curriculo' => ['file', 'mimes:doc,pdf,docx', 'max:5242880'], //5mb
        'data_ingresso' => ['nullable', 'date'],
        'situacao' => ['required', 'string', Rule::in(['ATIVO', 'AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LIDER', 'ESPECIALISTA', 'INATIVO'])],
      ],
      [
        'nome_razao.required' => 'Preencha o campo nome ou razão social',
        'cpf_cnpj.required' => 'Preencha o campo CPF',
        'cpf_cnpj.min' => 'CPF inválido',
        'cpf_cnpj.max' => 'CPF inválido',
        'curriculo.mimes' => 'Somente arquivos DOC, DOCX e PDF',
        'curriculo.max' => 'Tamanho máximo 5MB',
        'situacao.required' => 'Selecione uma situação.',
        'situacao.in' => 'Selecione uma situação válida.',
      ]
    );

    // se foi enviado currículo
    if ($request->hasFile('curriculo')) {
      $fileName = sanitizeFileName(pathinfo($request->file('curriculo')->getClientOriginalName(), PATHINFO_FILENAME));
      $extension = $request->file('curriculo')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('curriculo')->move(public_path('curriculos'), $fileName);
      $curriculo = 'curriculos/' . $fileName;
      $avaliador->update([
        'curriculo' => $curriculo
      ]);
    }

    $avaliador->update([
      'exp_min_comprovada' => $request->get('exp_min_comprovada') ?? 0,
      'curso_incerteza' => $request->get('curso_incerteza') ?? 0,
      'curso_iso' => $request->get('curso_iso') ?? 0,
      'curso_aud_interna' => $request->get('curso_aud_interna') ?? 0,
      'parecer_psicologico' => $request->get('parecer_psicologico') ?? 0,
      'data_ingresso' => $request->get('data_ingresso'),
      'situacao' => $request->get('situacao'),
    ]);

    $avaliador->pessoa->update([
      'nome_razao' => ucfirst($request->get('nome_razao')),
      'cpf_cnpj' => $request->get('cpf_cnpj'),
      'rg_ie' => $request->get('rg_ie'),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email')
    ]);

    return redirect()->back()->with('success', 'Avaliador atualizado com sucesso');
  }

  /**
   * Atualiza endereços do avaliador
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function updateEnderecos(Request $request, Avaliador $avaliador): RedirectResponse
  {

    $pessoalFields = [
        'pessoal_cep', 'pessoal_endereco', 'pessoal_bairro', 'pessoal_cidade', 'pessoal_uf'
    ];

    $comercialFields = [
        'comercial_cep', 'comercial_endereco', 'comercial_bairro', 'comercial_cidade', 'comercial_uf'
    ];

    $isPessoalGroupFilled = false;
    foreach ($pessoalFields as $field) {
        if ($request->filled($field)) {
            $isPessoalGroupFilled = true;
            break;
        }
    }

    $isComercialGroupFilled = false;
    foreach ($comercialFields as $field) {
        if ($request->filled($field)) {
            $isComercialGroupFilled = true;
            break;
        }
    }

    $rules = [
        'pessoal_cep' => ['nullable', 'string', 'max:9', 'min:9', Rule::requiredIf($isPessoalGroupFilled)],
        'pessoal_endereco' => ['nullable', 'string', 'max:255', Rule::requiredIf($isPessoalGroupFilled)],
        'pessoal_complemento' => 'nullable|string|max:255',
        'pessoal_bairro' => ['nullable', 'string', 'max:255', Rule::requiredIf($isPessoalGroupFilled)],
        'pessoal_cidade' => ['nullable', 'string', 'max:255', Rule::requiredIf($isPessoalGroupFilled)],
        'pessoal_uf' => ['nullable', 'string', 'max:2', Rule::requiredIf($isPessoalGroupFilled)],
        'comercial_cep' => ['nullable', 'string', 'max:9', 'min:9', Rule::requiredIf($isComercialGroupFilled)],
        'comercial_endereco' => ['nullable', 'string', 'max:255', Rule::requiredIf($isComercialGroupFilled)],
        'comercial_complemento' => 'nullable|string|max:255',
        'comercial_bairro' => ['nullable', 'string', 'max:255', Rule::requiredIf($isComercialGroupFilled)],
        'comercial_cidade' => ['nullable', 'string', 'max:255', Rule::requiredIf($isComercialGroupFilled)],
        'comercial_uf' => ['nullable', 'string', 'max:2', Rule::requiredIf($isComercialGroupFilled)],
    ];

    $messages = [
        'pessoal_cep.required' => 'O CEP  é obrigatório.',
        'pessoal_endereco.required' => 'O endereço é obrigatório.',
        'pessoal_bairro.required' => 'O bairro é obrigatório.',
        'pessoal_cidade.required' => 'A cidade é obrigatória.',
        'pessoal_uf.required' => 'O UF é obrigatório.',
        'comercial_cep.required' => 'O CEP é obrigatório.',
        'comercial_endereco.required' => 'O endereço comercial é obrigatório.',
        'comercial_bairro.required' => 'O bairro é obrigatório.',
        'comercial_cidade.required' => 'A cidade é obrigatória.',
        'comercial_uf.required' => 'O UF é obrigatório.',
        'pessoal_cep.min'     => 'O CEP deve conter exatamente 9 caracteres (incluindo o hífen).',
        'comercial_cep.min'   => 'O CEP deve conter exatamente 9 caracteres (incluindo o hífen).',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput()->withFragment('enderecos');
    }

    if ($isPessoalGroupFilled) {
        $avaliador->pessoa->enderecos()->updateOrCreate(
            [
                'pessoa_id' => $avaliador->pessoa->id,
                'avaliador_id' => null
            ],
            [   
                'info' => 'Endereço pessoal',
                'cep' => $request->pessoal_cep,
                'endereco' => $request->pessoal_endereco,
                'complemento' => $request->pessoal_complemento,
                'bairro' => $request->pessoal_bairro,
                'cidade' => $request->pessoal_cidade,
                'uf' => $request->pessoal_uf,
            ]
        );
    }

    if ($isComercialGroupFilled) {
        $avaliador->pessoa->enderecos()->updateOrCreate(
            [
                'pessoa_id' => $avaliador->pessoa->id,
                'avaliador_id' => $avaliador->id
            ],
            [
                'info' => 'Endereço comercial',
                'cep' => $request->comercial_cep,
                'endereco' => $request->comercial_endereco,
                'complemento' => $request->comercial_complemento,
                'bairro' => $request->comercial_bairro,
                'cidade' => $request->comercial_cidade,
                'uf' => $request->comercial_uf,
            ]
        );
    }

    return redirect()->back()->with('success', 'Endereços atualizados com sucesso')->withFragment('enderecos');
  }


  /**
   * Remove avaliador
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function delete(Avaliador $avaliador): RedirectResponse
  {
    if (File::exists(public_path($avaliador->curriculo))) {
      File::delete(public_path($avaliador->curriculo));
    }

    $avaliador->delete();

    return redirect()->route('avaliador-index')->with('warning', 'Avaliador removido');
  }

  /**
   * Remove arquivo de curriculo
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function curriculoDelete(Avaliador $avaliador): RedirectResponse
  {
    if (File::exists(public_path($avaliador->curriculo))) {
      File::delete(public_path($avaliador->curriculo));
    }

    $avaliador->update(['curriculo' => null]);

    return redirect()->back()->with('success', 'Curriculo removido');
  }


  /**
   * Adiciona avaliacao
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function createAvaliacao(Request $request, Avaliador $avaliador): RedirectResponse
  {
    $request->validate(
      [
        'empresa' => ['required', 'integer', 'exists:pessoas,id'],
        'situacao' => ['required', Rule::in(['AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LÍDER', 'ESPECIALISTA'])],
        'data' => ['required', 'date'],
      ],
      [
        'empresa.required' => 'O campo empresa é obrigatório.',
        'empresa.integer' => 'O campo empresa deve ser um número inteiro.',
        'empresa.exists' => 'A empresa selecionada é inválida.',
        'situacao.required' => 'Selecione uma situação válida.',
        'situacao.in' => 'A situação selecionada é inválida.',
        'data.required' => 'O campo data é obrigatório.',
        'data.date' => 'O campo data não é uma data válida.',
      ]
    );

    $avaliacao = AvaliacaoAvaliador::create([
      'avaliador_id' => $avaliador->id,
      'empresa' => $request->empresa,
      'data' => $request->data,
      'situacao' => $request->situacao,
      'inserido_por' => auth()->user()->name,
    ]);

    if (!$avaliacao) {
      return redirect()->back()
        ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('avaliador-insert', $avaliador->uid)
      ->with('success', 'Avaliação cadastrada com sucesso');
  }

  /**
   * atualiza avaliacao
   *
   * @param Request $request
   * @param AvaliacaoAvaliador $avaliacao
   * @return RedirectResponse
   **/
  public function updateAvaliacao(Request $request, AvaliacaoAvaliador $avaliacao): RedirectResponse
  {
    $request->validate(
      [
        'empresa' => ['required', 'integer', 'exists:pessoas,id'],
        'situacao' => ['required', Rule::in(['AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LÍDER', 'ESPECIALISTA'])],
        'data' => ['required', 'date'],
      ],
      [
        'empresa.required' => 'O campo empresa é obrigatório.',
        'empresa.integer' => 'O campo empresa deve ser um número inteiro.',
        'empresa.exists' => 'A empresa selecionada é inválida.',
        'situacao.required' => 'Selecione uma situação válida.',
        'situacao.in' => 'A situação selecionada é inválida.',
        'data.required' => 'O campo data é obrigatório.',
        'data.date' => 'O campo data não é uma data válida.',
      ]
    );

    $avaliacao->update([
      'empresa' => $request->empresa,
      'data' => $request->data,
      'situacao' => $request->situacao,
      'inserido_por' => auth()->user()->name,
    ]);

    return redirect()->back()->with('success', 'Avaliaçãp atualizada com sucesso');
  }

  /**
   * Remove avaliacao
   *
   * @param AvaliacaoAvaliador $avaliacao
   * @return RedirectResponse
   **/
  public function deleteAvaliacao(AvaliacaoAvaliador $avaliacao): RedirectResponse
  {

    $avaliacao->delete();

    return redirect()->back()->with('warning', 'Avaliação removida');
  }


  /**
   * Adiciona qualificação
   *
   * @param Avaliador $avaliador
   * @param Request $request
   * @return RedirectResponse
   **/
  public function createQualificacao(Avaliador $avaliador, Request $request): RedirectResponse
  {
    $validatedData = $request->validate([
      'ano' => ['nullable', 'string'],
      'atividade' => ['nullable', 'string'],
    ], [
      'ano.string' => 'O campo Ano deve ser uma string.',
      'atividade.string' => 'O campo Atividade deve ser uma string.',
    ]);

    if (!$avaliador) {
      return redirect()->back()->with('error', 'Houve um erro, tente novamente');
    }

    $validatedData['avaliador_id'] = $avaliador->id;

    Qualificacao::create($validatedData);

    return redirect()->back()->with('success', 'Qualificação cadastrada com sucesso');
  }

  /**
   * Atualiza qualificação
   *
   * @param Qualificacao $qualificacao
   * @param Request $request
   * @return RedirectResponse
   **/
  public function updateQualificacao(Request $request, Qualificacao $qualificacao): RedirectResponse
  {
    $validatedData = $request->validate([
      'ano' => 'nullable|string',
      'atividade' => 'nullable|string',
    ], [
      'ano.string' => 'O campo Ano deve ser uma string.',
      'atividade.string' => 'O campo Atividade deve ser uma string.',
    ]);

    $qualificacao->update($validatedData);

    return redirect()->back()->with('success', 'Qualificação atualizada com sucesso');
  }

  /**
   * Remove qualificacao do avaliador
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function deleteQualificacao(Qualificacao $qualificacao): RedirectResponse
  {

    $qualificacao->delete();

    return redirect()->back()->with('warning', 'Qualificação removida');
  }

  /**
   * Adiciona area de atuação
   *
   * @param Avaliador $avaliador
   * @param Request $request
   * @return RedirectResponse
   **/
  public function createArea(Avaliador $avaliador, Request $request): RedirectResponse
  {
    $validatedData = $request->validate([
      'area_id' => ['required', 'exists:areas_atuacao,id'],
      'situacao' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
      'data_cadastro' => ['nullable', 'date'],
    ], [
      'area_id.required' => 'Selecione uma opção válida',
      'area_id.exists' => 'Selecione uma opção válida',
      'situacao.in' => 'Selecione uma opção válida',
      'data_cadastro.date' => 'Data inválida'
    ]);

    if (!$avaliador) {
      return redirect()->back()->with('error', 'Houve um erro, tente novamente');
    }

    $validatedData['avaliador_id'] = $avaliador->id;

    AvaliadorArea::create($validatedData);

    return redirect()->back()->with('success', 'Área cadastrada com sucesso');
  }

  /**
   * Atualiza area de atuação do avaliador
   *
   * @param AvaliadorArea $area
   * @param Request $request
   * @return RedirectResponse
   **/
  public function updateArea(Request $request, AvaliadorArea $area): RedirectResponse
  {
    $validatedData = $request->validate([
      'area_id' => ['required', 'exists:areas_atuacao,id'],
      'situacao' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
      'data_cadastro' => ['nullable', 'date'],
    ], [
      'area_id.required' => 'Selecione uma opção válida',
      'area_id.exists' => 'Selecione uma opção válida',
      'situacao.in' => 'Selecione uma opção válida',
      'data_cadastro.date' => 'Data inválida'
    ]);

    $area->update($validatedData);

    return redirect()->back()->with('success', 'Área atualizada com sucesso');
  }

  /**
   * Remove area de atuação do avaliador
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function deleteArea(AvaliadorArea $area): RedirectResponse
  {
    $area->delete();

    return redirect()->back()->with('warning', 'Qualificação removida');
  }

  
  /**
   * Adiciona status
   *
   * @param Avaliador $avaliador
   * @param Request $request
   * @return RedirectResponse
   **/
  public function createStatus(Avaliador $avaliador, Request $request): RedirectResponse
  {
    $validatedData = $request->validate([
      'data' => ['nullable', 'date'],
      'status' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
      'parecer_positivo' => ['nullable', 'in:0,1'],
    ], [
      'data.date' => ' Data inválida',
      'status.in' => 'Selecione uma opção válida',
      'parecer_positivo.in' => 'Selecione uma opção válida',
    ]);

    if (!$avaliador) {
      return redirect()->back()->with('error', 'Houve um erro, tente novamente');
    }

    $validatedData['avaliador_id'] = $avaliador->id;

    StatusAvaliador::create($validatedData);

    return redirect()->back()->with('success', 'Status cadastrado com sucesso');
  }

  /**
   * Atualiza status
   *
   * @param StatusAvaliador $status
   * @param Request $request
   * @return RedirectResponse
   **/
  public function updateStatus(Request $request, StatusAvaliador $status): RedirectResponse
  {
    $validatedData = $request->validate([
      'data' => ['nullable', 'date'],
      'status' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
      'parecer_positivo' => ['nullable', 'in:0,1'],
    ], [
      'data.date' => ' Data inválida',
      'status.in' => 'Selecione uma opção válida',
      'parecer_positivo.in' => 'Selecione uma opção válida',
    ]);

    $status->update($validatedData);

    return redirect()->back()->with('success', 'Status atualizado com sucesso');
  }

  /**
   * Remove status
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function deleteStatus(StatusAvaliador $status): RedirectResponse
  {
    $status->delete();

    return redirect()->back()->with('warning', 'Status removido');
  }
}
