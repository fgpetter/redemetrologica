<div class="card">
    <div class="card-body">
        <div class="row gx-3 align-items-center">
            <div class="col-auto">
                <x-forms.input-field :value="old('data_inicial') ?? ($lancamento_financeiro->data_inicial ?? null)" 
                    type="date" name="data_inicial" id="data_inicial" label="Data Inicial" />
            </div>
            <div class="col-auto">
                <x-forms.input-field :value="old('data_inicial') ?? ($lancamento_financeiro->data_inicial ?? null)" 
                    type="date" name="data_inicial" id="data_inicial" label="Data Inicial" />
            </div>
            <div class="col-2">
                <x-forms.input-select name="tipo" label="Tipo" tooltip="Ao selecionar um curso ou PEP esse campo será desconsiderado">
                    <option> - </option>
                    <option value="CURSO">CURSO</option>
                    <option value="PEP">PEP</option>
                    <option value="AVALIACAO">AVALIAÇÃO</option>
                </x-forms.input-select>
            </div>
        </div>
        <div class="row mt-2 align-items-end">
            <div class="col-5">
                <x-forms.input-select name="curso" id="curso" label="Curso">
                    <option> - </option>
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->id }}">{{ $curso->id }} - {{ $curso->curso->descricao }}</option>                                
                    @endforeach
                </x-forms.input-select>
            </div>
            <div class="col-5">
                <x-forms.input-select name="pep" id="pep" label="PEP">
                    <option> - </option>
                </x-forms.input-select>
            </div>
        </div>
        <div class="row mt-2 align-items-end">
            <div class="col-10">
                <x-forms.input-select name="pessoa" id="pessoa" label="Pessoa">
                    <option> - </option>
                    @foreach ($pessoas as $pessoa)
                        <option value="{{ $pessoa->id }}">{{ $pessoa->cpf_cnpj }} - {{ $pessoa->nome_razao }}</option>                                
                    @endforeach
                </x-forms.input-select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary px-3 py-2 mx-sm-3">Pesquisar</button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0"
                style="table-layout: fixed">
                <thead>
                    <tr>
                        <th scope="col" style="width: 50%; white-space: nowrap;">Nome</th>
                        <th scope="col">Emissão</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Valor</th>
                        <th scope="col">Pagamento</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lancamentosfinanceiros as $lancamento)
                        <tr>
                            <td class="text-truncate">
                                <a data-bs-toggle="collapse" href="{{"#collapse".$lancamento->uid}}" role="button" aria-expanded="false" aria-controls="collapseExample">
                                    <i class="ri-file-text-line btn-ghost ps-2 pe-3 fs-5"></i>
                                </a> {{ $lancamento->pessoa->nome_razao }}
                            </td>
                            <td>{{ ($lancamento->data_emissao) ? Carbon\Carbon::parse($lancamento->data_emissao)->format('d/m/Y') : '-'  }} </td>
                            <td>{!! ($lancamento->tipo_lancamento == 'CREDITO') ? 
                                "<span class='badge rounded-pill bg-success'>Crédito</span>" : 
                                "<span class='badge rounded-pill bg-warning'>Débito</span>"!!}</td>
                            <td> <input type="text" class="money border-0 bg-transparent" value="{{ $lancamento->valor }}"> </td>
                            <td> {!! ($lancamento->data_pagamento) ? Carbon\Carbon::parse($lancamento->data_pagamento)->format('d/m/Y') : "<span class='badge rounded-pill bg-warning'>Em Aberto</span>" !!} </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                        <li><a class="dropdown-item"
                                                href="{{ route('lancamento-financeiro-insert', ['lancamento' => $lancamento->uid]) }}">Editar</a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route="lancamento-financeiro-delete"
                                                id="{{ $lancamento->uid }}" />
                                        </li>
                                    </ul>
                                </div>

                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class="p-0">
                                <div class="collapse" id="{{"collapse".$lancamento->uid}}">
                                    <div class="row gy-2 m-3 mt-2">
                                        <div class="col-12"><b>Historico:</b> {{ $lancamento->historico ?? '-' }}</div>
                                        <div class="col-2"><b>Vencimento:</b> {{ ($lancamento->data_vencimento) ? Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : '-'  }}</div>
                                        <div class="col-2"><b>Documento:</b> {{ $lancamento->documento ?? '-' }}</div>
                                        <div class="col-2"><b>Nº Documento:</b> {{ $lancamento->num_documento ?? '-' }}</div>
                                        <div class="col-2"><b>Status:</b> {{ $lancamento->status ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center"> Não há lançamentos na base. </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="row mt-3">
                {!! $lancamentosfinanceiros->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div>

    </div>
</div>
