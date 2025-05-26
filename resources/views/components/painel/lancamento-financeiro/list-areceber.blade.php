@php
    if (isset($_GET['data_inicial']) && $_GET['data_inicial'] != '') {
        $data_inicial = \Carbon\Carbon::parse($_GET['data_inicial'])->format('Y-m-d');
    }

    if (isset($_GET['data_final']) && $_GET['data_final'] != '') {
        $data_final = \Carbon\Carbon::parse($_GET['data_final'])->format('Y-m-d');
    }

    isset($_GET['pessoa']) && $_GET['pessoa'] != '' ? ($busca_pessoa = $_GET['pessoa']) : ($busca_pessoa = null);
    isset($_GET['curso']) && $_GET['curso'] != '' ? ($busca_curso = $_GET['curso']) : ($busca_curso = null);
    isset($_GET['pep']) && $_GET['pep'] != '' ? ($busca_pep = $_GET['pep']) : ($busca_pep = null);
@endphp

<div class="card">
    <div class="card-body">
        <form method="GET">
            <div class="row gx-3 align-items-center">
                <div class="col-auto">
                    <x-forms.input-field :value="old('data_inicial') ?? ($data_inicial ?? null)" type="date" name="data_inicial" id="data_inicial"
                        label="Data Inicial" />
                </div>
                <div class="col-auto">
                    <x-forms.input-field :value="old('data_final') ?? ($data_final ?? null)" type="date" name="data_final" id="data_final"
                        label="Data Final" />
                </div>
                <div class="col-2">
                    <x-forms.input-select name="area" label="Área"
                        tooltip="Ao selecionar um curso ou PEP esse campo será desconsiderado">
                        <option value=""> - </option>
                        <option @selected(isset($_GET['area']) && $_GET['area'] == 'CURSO') value="CURSO">CURSO</option>
                        <option @selected(isset($_GET['area']) && $_GET['area'] == 'PEP') value="PEP">PEP</option>
                        <option @selected(isset($_GET['area']) && $_GET['area'] == 'AVALIACAO') value="AVALIACAO">AVALIAÇÃO</option>
                    </x-forms.input-select>
                </div>
            </div>
            <div class="row mt-2 align-items-end">
                <div class="col-5">
                    <x-forms.input-select name="curso" id="curso" label="Curso">
                        <option value=""> - </option>
                        @foreach ($cursos as $curso)
                            <option @selected($curso->id == $busca_curso) value="{{ $curso->id }}">
                                ID: {{ $curso->id }} - {{ $curso->curso->descricao }}
                            </option>
                        @endforeach
                    </x-forms.input-select>
                </div>
                <div class="col-5">
                    <x-forms.input-select name="pep" id="pep" label="PEP">
                        <option value=""> - </option>
                        @foreach ($agendainterlabs as $agendainterlab)
                            <option @selected($agendainterlab->id == $busca_pep) value="{{ $agendainterlab->id }}">
                                {{ $agendainterlab->interlab->nome }}</option>
                        @endforeach

                    </x-forms.input-select>
                </div>
            </div>
            <div class="row mt-2 align-items-end">
                <div class="col-10">
                    <x-forms.input-select name="pessoa" id="pessoa" label="Pessoa">
                        <option value=""> - </option>
                        @foreach ($pessoas as $pessoa)
                            <option @selected($pessoa->id == $busca_pessoa) value="{{ $pessoa->id }}">{{ $pessoa->nome_razao }} -
                                {{ $pessoa->cpf_cnpj }}</option>
                        @endforeach
                    </x-forms.input-select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary px-3 py-2 ms-sm-3">Pesquisar</button>
                    <a href="{{ route('a-receber-index') }}" class="btn btn-sm btn-danger px-3 py-2">Limpar</a>
                </div>
            </div>
        </form>
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
                        <th scope="col">Vencimento</th>
                        <th scope="col">Área</th>
                        <th scope="col">Valor</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lancamentosfinanceiros as $lancamento)
                        <tr>
                            <td class="text-truncate">
                                <a data-bs-toggle="collapse" href="{{ '#collapse' . $lancamento->uid }}" role="button"
                                    aria-expanded="false" aria-controls="collapseExample">
                                    <i class="ri-file-text-line btn-ghost ps-2 pe-3 fs-5"></i>
                                </a> {{ $lancamento->pessoa->nome_razao }}
                            </td>
                            <td>{{ $lancamento->data_vencimento ? Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                {{ $lancamento->agenda_curso_id ? 'CURSO' : '' }}
                                {{ $lancamento->agenda_interlab_id ? 'PEP' : '' }}
                                {{ $lancamento->agenda_avaliacao_id ? 'AVALIAÇÃO' : '' }}
                            </td>
                            <td>
                                <input type="text" class=" border-0 bg-transparent"
                                    value="{{ number_format($lancamento->valor ?? 0, 2, ',', '.') }}">
                            </td>
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
                            <td colspan="5" class="p-0">
                                <div class="collapse" id="{{ 'collapse' . $lancamento->uid }}">
                                    <div class="row gy-2 m-3 mt-2">
                                        <div class="col-12"><b>Historico:</b> {{ $lancamento->historico ?? '-' }}</div>
                                        <div class="col-2"><b>Status:</b> {{ $lancamento->status ?? '-' }}</div>
                                        <div class="col-2"><b>Centro Custo:</b>
                                            {{ $lancamento->centroCusto?->descricao ?? '-' }}</div>
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
            <div class="row mt-3 w-100">
                {!! $lancamentosfinanceiros->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div>

    </div>
</div>
