<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtualizarAvaliadorRequest;
use App\Http\Requests\AtualizarEnderecosAvaliadorRequest;
use App\Http\Requests\CriarAvaliadorRequest;
use App\Models\AreaAtuacao;
use App\Models\AvaliacaoAvaliador;
use App\Models\Avaliador;
use App\Models\AvaliadorArea;
use App\Models\Pessoa;
use App\Models\Qualificacao;
use App\Models\StatusAvaliador;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class AvaliadorController extends Controller
{
    /**
     * Gera pagina de listagem de avaliadores
     *
     **/
    public function index(Request $request): View
    {
        $order = in_array($request->input('name'), ['asc', 'desc'], true) ? $request->input('name') : 'asc';
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

        return view('painel.avaliadores.index', [
            'avaliadores' => $avaliadores,
        ]);
    }

    /**
     * Adiciona avaliadores na base
     *
     **/
    public function create(CriarAvaliadorRequest $request): RedirectResponse
    {

        $pessoa = Pessoa::where('uid', $request->pessoa_uid)->first();

        $validated = $request->validated();

        $avaliador = DB::transaction(function () use ($request, $pessoa, $validated) {
            $avaliador = Avaliador::create([
                'pessoa_id' => $pessoa->id,
                'exp_min_comprovada' => $validated['exp_min_comprovada'] ?? 0,
                'curso_incerteza' => $validated['curso_incerteza'] ?? 0,
                'curso_iso' => $validated['curso_iso'] ?? 0,
                'curso_aud_interna' => $validated['curso_aud_interna'] ?? 0,
                'parecer_psicologico' => $validated['parecer_psicologico'] ?? 0,
                'data_ingresso' => $validated['data_ingresso'] ?? null,
                'situacao' => $validated['situacao'],
            ]);

            if (! $avaliador) {
                return null;
            }

            // se foi enviado currículo
            if ($request->hasFile('curriculo')) {
                $fileName = sanitizeFileName(pathinfo($request->file('curriculo')->getClientOriginalName(), PATHINFO_FILENAME));
                $extension = $request->file('curriculo')->getClientOriginalExtension();
                $fileName = $fileName.'_'.time().'.'.$extension;
                $request->file('curriculo')->move(public_path('curriculos'), $fileName);
                $avaliador->update([
                    'curriculo' => 'curriculos/'.$fileName,
                ]);
            }

            $pessoa->update([
                'cpf_cnpj' => $validated['cpf_cnpj'] ?? null,
                'rg_ie' => $validated['rg_ie'] ?? null,
                'telefone' => $validated['telefone'] ?? null,
                'email' => $validated['email'] ?? null,
            ]);

            return $avaliador;
        });

        if (! $avaliador) {
            return redirect()->back()->with('avaliador-error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        return redirect()->route('avaliador-insert', $avaliador->uid)
            ->with('success', 'Avaliador cadastrado com sucesso');
    }

    /**
     * Tela de edição de avaliador
     *
     **/
    public function insert(Avaliador $avaliador): View
    {
        // carrega avaliações que esse avaliador realizou
        $avaliacoes = AvaliacaoAvaliador::with('agendaAvaliacao')->where('avaliador_id', $avaliador->id)->get();

        // carrega qualificações do avaliador
        $qualificacoes = Qualificacao::where('avaliador_id', $avaliador->id)->get();
        $qualificacoes_list = [
            'atividades' => DB::table('avaliador_qualificacoes')->distinct()->get(['atividade']),
            'instrutores' => DB::table('avaliador_qualificacoes')->distinct()->get(['instrutor']),
        ];

        // carrega areas de atuação do avaliador
        $areas_atuacao = AreaAtuacao::select('id', 'uid', 'descricao')->get();

        // carrega endereço pessoal e comercial do avaliador (avaliador novo ainda não tem pessoa vinculada)
        $endereco_pessoal = null;
        $endereco_comercial = null;
        if ($avaliador->pessoa) {
            $endereco_pessoal = $avaliador->pessoa->enderecos()
                ->where('pessoa_id', $avaliador->pessoa_id)
                ->whereNull('avaliador_id')
                ->first();

            $endereco_comercial = $avaliador->pessoa->enderecos()
                ->where('avaliador_id', $avaliador->id)
                ->first();
        }

        // carrega lista com empresas
        $empresas = Pessoa::select('id', 'uid', 'nome_razao', 'cpf_cnpj')
            ->where('tipo_pessoa', 'PJ')
            ->orderBy('nome_razao')
            ->get();

        // avaliador novo: carrega pessoas físicas ainda não vinculadas a avaliadores, para o select da tela de inserção
        $pessoas = collect();
        if (! $avaliador->exists) {
            $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj', 'rg_ie', 'telefone', 'email')
                ->where('tipo_pessoa', 'PF')
                ->whereNotIn('id', function ($query) {
                    $query->select('pessoa_id')->from('avaliadores');
                })
                ->get();
        }

        return view(
            'painel.avaliadores.edit',
            [
                'avaliador' => $avaliador,
                'avaliacoes' => $avaliacoes,
                'qualificacoes' => $qualificacoes,
                'qualificacoes_list' => $qualificacoes_list,
                'areas_atuacao' => $areas_atuacao,
                'endereco_pessoal' => $endereco_pessoal,
                'endereco_comercial' => $endereco_comercial,
                'empresas' => $empresas,
                'pessoas' => $pessoas,
            ]
        );
    }

    /**
     * Edita dados de avaliador
     *
     * @param  Avaliador  $user
     **/
    public function update(AtualizarAvaliadorRequest $request, Avaliador $avaliador): RedirectResponse
    {

        $validated = $request->validated();

        DB::transaction(function () use ($request, $avaliador, $validated) {
            // se foi enviado currículo
            if ($request->hasFile('curriculo')) {
                $fileName = sanitizeFileName(pathinfo($request->file('curriculo')->getClientOriginalName(), PATHINFO_FILENAME));
                $extension = $request->file('curriculo')->getClientOriginalExtension();
                $fileName = $fileName.'_'.time().'.'.$extension;
                $request->file('curriculo')->move(public_path('curriculos'), $fileName);
                $curriculo = 'curriculos/'.$fileName;
                $avaliador->update([
                    'curriculo' => $curriculo,
                ]);
            }

            $avaliador->update([
                'exp_min_comprovada' => $validated['exp_min_comprovada'] ?? 0,
                'curso_incerteza' => $validated['curso_incerteza'] ?? 0,
                'curso_iso' => $validated['curso_iso'] ?? 0,
                'curso_aud_interna' => $validated['curso_aud_interna'] ?? 0,
                'parecer_psicologico' => $validated['parecer_psicologico'] ?? 0,
                'data_ingresso' => $validated['data_ingresso'] ?? null,
                'situacao' => $validated['situacao'],
            ]);

            $avaliador->pessoa->update([
                'nome_razao' => ucfirst($validated['nome_razao']),
                'cpf_cnpj' => $validated['cpf_cnpj'] ?? null,
                'rg_ie' => $validated['rg_ie'] ?? null,
                'telefone' => $validated['telefone'] ?? null,
                'email' => $validated['email'] ?? null,
            ]);

        });

        return redirect()->back()->with('success', 'Avaliador atualizado com sucesso');
    }

    /**
     * Atualiza endereços do avaliador
     *
     **/
    public function updateEnderecos(AtualizarEnderecosAvaliadorRequest $request, Avaliador $avaliador): RedirectResponse
    {

        $pessoalFields = [
            'pessoal_cep', 'pessoal_endereco', 'pessoal_bairro', 'pessoal_cidade', 'pessoal_uf',
        ];

        $comercialFields = [
            'comercial_cep', 'comercial_endereco', 'comercial_bairro', 'comercial_cidade', 'comercial_uf',
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

        $validated = $request->validated();

        DB::transaction(function () use ($avaliador, $validated, $isPessoalGroupFilled, $isComercialGroupFilled) {
            if ($isPessoalGroupFilled) {
                $avaliador->pessoa->enderecos()->updateOrCreate(
                    [
                        'pessoa_id' => $avaliador->pessoa->id,
                        'avaliador_id' => null,
                    ],
                    [
                        'info' => 'Endereço pessoal',
                        'cep' => $validated['pessoal_cep'] ?? null,
                        'endereco' => $validated['pessoal_endereco'] ?? null,
                        'complemento' => $validated['pessoal_complemento'] ?? null,
                        'bairro' => $validated['pessoal_bairro'] ?? null,
                        'cidade' => $validated['pessoal_cidade'] ?? null,
                        'uf' => $validated['pessoal_uf'] ?? null,
                    ]
                );
            }

            if ($isComercialGroupFilled) {
                $avaliador->pessoa->enderecos()->updateOrCreate(
                    [
                        'pessoa_id' => $avaliador->pessoa->id,
                        'avaliador_id' => $avaliador->id,
                    ],
                    [
                        'info' => 'Endereço comercial',
                        'cep' => $validated['comercial_cep'] ?? null,
                        'endereco' => $validated['comercial_endereco'] ?? null,
                        'complemento' => $validated['comercial_complemento'] ?? null,
                        'bairro' => $validated['comercial_bairro'] ?? null,
                        'cidade' => $validated['comercial_cidade'] ?? null,
                        'uf' => $validated['comercial_uf'] ?? null,
                    ]
                );
            }

        });

        return redirect()->back()->with('success', 'Endereços atualizados com sucesso')->withFragment('enderecos');
    }

    /**
     * Remove avaliador
     *
     * @param  User  $user
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
     * @param  User  $user
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
     **/
    public function createAvaliacao(Request $request, Avaliador $avaliador): RedirectResponse
    {
        $request->validateWithBag('avaliacao_nova',
            [
                'empresa' => ['required', 'integer', 'exists:pessoas,id'],
                'situacao' => ['required', Rule::in(['AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LÍDER', 'ESPECIALISTA'])],
                'data' => ['required', Rule::date()->format('Y-m-d')],
            ],
            [
                'empresa.required' => 'O campo empresa é obrigatório.',
                'empresa.integer' => 'O campo empresa deve ser um número inteiro.',
                'empresa.exists' => 'A empresa selecionada é inválida.',
                'situacao.required' => 'Selecione uma situação válida.',
                'situacao.in' => 'A situação selecionada é inválida.',
                'data.required' => 'O campo data é obrigatório.',
                'data.date' => 'O campo data não é uma data válida.',
                'data.date_format' => 'O campo data não é uma data válida.',
            ]
        );

        $avaliacao = AvaliacaoAvaliador::create([
            'avaliador_id' => $avaliador->id,
            'empresa' => $request->empresa,
            'data' => $request->data,
            'situacao' => $request->situacao,
            'inserido_por' => auth()->user()->name,
        ]);

        if (! $avaliacao) {
            return redirect()->back()
                ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        return redirect()->route('avaliador-insert', $avaliador->uid)
            ->with('success', 'Avaliação cadastrada com sucesso')->withFragment('avaliacoes');
    }

    /**
     * atualiza avaliacao
     *
     **/
    public function updateAvaliacao(Request $request, AvaliacaoAvaliador $avaliacao): RedirectResponse
    {
        $request->validateWithBag('avaliacao_'.$avaliacao->uid,
            [
                'empresa' => ['required', 'integer', 'exists:pessoas,id'],
                'situacao' => ['required', Rule::in(['AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LÍDER', 'ESPECIALISTA'])],
                'data' => ['required', Rule::date()->format('Y-m-d')],
            ],
            [
                'empresa.required' => 'O campo empresa é obrigatório.',
                'empresa.integer' => 'O campo empresa deve ser um número inteiro.',
                'empresa.exists' => 'A empresa selecionada é inválida.',
                'situacao.required' => 'Selecione uma situação válida.',
                'situacao.in' => 'A situação selecionada é inválida.',
                'data.required' => 'O campo data é obrigatório.',
                'data.date' => 'O campo data não é uma data válida.',
                'data.date_format' => 'O campo data não é uma data válida.',
            ]
        );

        $avaliacao->update([
            'empresa' => $request->empresa,
            'data' => $request->data,
            'situacao' => $request->situacao,
            'inserido_por' => auth()->user()->name,
        ]);

        return redirect()->back()->with('success', 'Avaliaçãp atualizada com sucesso')->withFragment('avaliacoes');
    }

    /**
     * Remove avaliacao
     *
     **/
    public function deleteAvaliacao(AvaliacaoAvaliador $avaliacao): RedirectResponse
    {

        $avaliacao->delete();

        return redirect()->back()->with('warning', 'Avaliação removida');
    }

    /**
     * Adiciona qualificação
     *
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

        if (! $avaliador) {
            return redirect()->back()->with('error', 'Houve um erro, tente novamente')->withFragment('qualificacoes');
        }

        $validatedData['avaliador_id'] = $avaliador->id;

        Qualificacao::create($validatedData);

        return redirect()->back()->with('success', 'Qualificação cadastrada com sucesso');
    }

    /**
     * Atualiza qualificação
     *
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

        return redirect()->back()->with('success', 'Qualificação atualizada com sucesso')->withFragment('qualificacoes');
    }

    /**
     * Remove qualificacao do avaliador
     *
     * @param  User  $user
     **/
    public function deleteQualificacao(Qualificacao $qualificacao): RedirectResponse
    {

        $qualificacao->delete();

        return redirect()->back()->with('warning', 'Qualificação removida');
    }

    /**
     * Adiciona area de atuação
     *
     **/
    public function createArea(Avaliador $avaliador, Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'area_id' => ['required', 'exists:areas_atuacao,id'],
            'situacao' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
            'data_cadastro' => ['nullable', Rule::date()->format('Y-m-d')],
        ], [
            'area_id.required' => 'Selecione uma opção válida',
            'area_id.exists' => 'Selecione uma opção válida',
            'situacao.in' => 'Selecione uma opção válida',
            'data_cadastro.date' => 'Data inválida',
            'data_cadastro.date_format' => 'Data inválida',
        ]);

        if (! $avaliador) {
            return redirect()->back()->with('error', 'Houve um erro, tente novamente')->withFragment('areasatuacao');
        }

        $validatedData['avaliador_id'] = $avaliador->id;

        AvaliadorArea::create($validatedData);

        return redirect()->back()->with('success', 'Área cadastrada com sucesso');
    }

    /**
     * Atualiza area de atuação do avaliador
     *
     **/
    public function updateArea(Request $request, AvaliadorArea $area): RedirectResponse
    {
        $validatedData = $request->validate([
            'area_id' => ['required', 'exists:areas_atuacao,id'],
            'situacao' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
            'data_cadastro' => ['nullable', Rule::date()->format('Y-m-d')],
        ], [
            'area_id.required' => 'Selecione uma opção válida',
            'area_id.exists' => 'Selecione uma opção válida',
            'situacao.in' => 'Selecione uma opção válida',
            'data_cadastro.date' => 'Data inválida',
            'data_cadastro.date_format' => 'Data inválida',
        ]);

        $area->update($validatedData);

        return redirect()->back()->with('success', 'Área atualizada com sucesso')->withFragment('areasatuacao');
    }

    /**
     * Remove area de atuação do avaliador
     *
     * @param  User  $user
     **/
    public function deleteArea(AvaliadorArea $area): RedirectResponse
    {
        $area->delete();

        return redirect()->back()->with('warning', 'Qualificação removida');
    }

    /**
     * Adiciona status
     *
     **/
    public function createStatus(Avaliador $avaliador, Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'data' => ['nullable', Rule::date()->format('Y-m-d')],
            'status' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
            'parecer_positivo' => ['nullable', 'in:0,1'],
        ], [
            'data.date' => ' Data inválida',
            'data.date_format' => ' Data inválida',
            'status.in' => 'Selecione uma opção válida',
            'parecer_positivo.in' => 'Selecione uma opção válida',
        ]);

        if (! $avaliador) {
            return redirect()->back()->with('error', 'Houve um erro, tente novamente');
        }

        $validatedData['avaliador_id'] = $avaliador->id;

        StatusAvaliador::create($validatedData);

        return redirect()->back()->with('success', 'Status cadastrado com sucesso')->withFragment('controlestatus');
    }

    /**
     * Atualiza status
     *
     **/
    public function updateStatus(Request $request, StatusAvaliador $status): RedirectResponse
    {
        $validatedData = $request->validate([
            'data' => ['nullable', Rule::date()->format('Y-m-d')],
            'status' => ['nullable', 'in:ATIVO,AVALIADOR,AVALIADOR EM TREINAMENTO,AVALIADOR LIDER,ESPECIALISTA,INATIVO'],
            'parecer_positivo' => ['nullable', 'in:0,1'],
        ], [
            'data.date' => ' Data inválida',
            'data.date_format' => ' Data inválida',
            'status.in' => 'Selecione uma opção válida',
            'parecer_positivo.in' => 'Selecione uma opção válida',
        ]);

        $status->update($validatedData);

        return redirect()->back()->with('success', 'Status atualizado com sucesso')->withFragment('controlestatus');
    }

    /**
     * Remove status
     *
     * @param  User  $user
     **/
    public function deleteStatus(StatusAvaliador $status): RedirectResponse
    {
        $status->delete();

        return redirect()->back()->with('warning', 'Status removido');
    }
}
