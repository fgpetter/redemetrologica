@php
    if (isset($_GET['data_inicial']) && $_GET['data_inicial'] != '') {
        $data_inicial = \Carbon\Carbon::parse($_GET['data_inicial'])->format('Y-m-d');
    } else {
        $data_inicial = \Carbon\Carbon::now()->format('Y-m-d');
    }

    if (isset($_GET['data_final']) && $_GET['data_final'] != '') {
        $data_final = \Carbon\Carbon::parse($_GET['data_final'])->format('Y-m-d');
    } else {
        $data_final = \Carbon\Carbon::now()->addDays(7)->format('Y-m-d');
    }

    isset($_GET['pessoa']) && $_GET['pessoa'] != '' ? ($busca_pessoa = $_GET['pessoa']) : ($busca_pessoa = null);
@endphp

<div class="row my-3">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <form method="GET">
                    <div class="row align-items-end">
                        <div class="col-2">
                            <x-forms.input-field :value="$data_inicial ?? null" type="date" name="data_inicial" id="data_inicial"
                                label="Data Inicial" />
                        </div>
                        <div class="col-2">
                            <x-forms.input-field :value="$data_final ?? null" type="date" name="data_final" id="data_final"
                                label="Data Final" />
                        </div>
                        <div class="col-12 col-sm-5 col-xxl-6">
                            <x-forms.input-select name="pessoa" id="pessoa" label="Pessoa">
                                <option value=""> - </option>
                                @foreach ($pessoas as $pessoa)
                                    <option @selected($busca_pessoa == $pessoa->id) value="{{ $pessoa->id }}">
                                        {{ $pessoa->cpf_cnpj }} - {{ $pessoa->nome_razao }}</option>
                                @endforeach
                            </x-forms.input-select>
                        </div>

                        <div class="col-3 col-xxl-2 d-inline-flex">
                            <button type="submit" class="btn btn-sm btn-primary px-3 py-2 me-sm-2">Pesquisar</button>

                            <a href="{{ route('lancamento-financeiro-insert') }}"
                                class="btn btn-sm btn-success px-3 py-2">
                                <i class="ri-add-line align-bottom me-1"></i> Adicionar
                            </a>
                        </div>
                        {{-- Exibe mes/ano dos últimos 12 meses --}}
                        <div class="col-3">
                            <label for="mesAnoExport" class="form-label mt-2">Exportar Lançamentos do Mês</label>
                            <div class="input-group">
                                <select class="form-select" id="mesAnoExport" name="mesAnoExport">
                                    <option value="">Selecione...</option>
                                    @for ($i = 0; $i < 12; $i++)
                                        @php
                                            $data = \Carbon\Carbon::now()->subMonths($i);
                                            $mes = str_pad($data->month, 2, '0', STR_PAD_LEFT);
                                            $ano = $data->year;
                                            $ultimoDiaMes = \Carbon\Carbon::create($ano, $mes, 1)->endOfMonth();
                                            if ($i === 0 && $ultimoDiaMes->isFuture()) {
                                                continue;
                                            }
                                        @endphp
                                        <option value="{{ $mes }}/{{ $ano }}">
                                            {{ $mes }}/{{ $ano }}</option>
                                    @endfor
                                </select>

                                <a href="#" class="btn btn-outline-success disabled" id="linkExportLancamentos">
                                    <i class="ri-download-2-line"></i> Baixar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
<div class="row">

    {{-- RECEITAS --}}
    <div class="col-6">
        <div class="card border-start border-success border-4">
            <div class="card-header bg-success-subtle">
                <h5>RECEITAS</h5>
            </div>
            <div class="card-body">


                <div class="table-responsive" style="min-height: 25vh">
                    <table class="table table-responsive border-1" style="table-layout: fixed">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 50%; white-space: nowrap;">Nome</th>
                                <th scope="col" style="white-space: nowrap;">Vencimento</th>
                                <th scope="col" style="white-space: nowrap;">Valor</th>
                                <th scope="col" style="white-space: nowrap;">Pagamento</th>
                                <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lancamentosfinanceiros->where('tipo_lancamento', 'CREDITO')->whereNotNull('data_pagamento') as $lancamento)
                                <tr>
                                    <td class="text-truncate">
                                        <a data-bs-toggle="collapse" href="{{ '#collapse' . $lancamento->uid }}"
                                            role="button" aria-expanded="false" aria-controls="collapseExample">
                                            <i class="ri-file-text-line btn-ghost ps-2 pe-3 fs-5"></i>
                                        </a> {{ $lancamento->pessoa->nome_razao }}
                                    </td>
                                    <td>{{ $lancamento->data_vencimento ? Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : '-' }}
                                    </td>

                                    <td> <input type="text" class="money border-0 bg-transparent"
                                            value="{{ $lancamento->valor }}"> </td>
                                    <td> {!! $lancamento->data_pagamento
                                        ? Carbon\Carbon::parse($lancamento->data_pagamento)->format('d/m/Y')
                                        : "<span class='badge rounded-pill bg-warning'>Em Aberto</span>" !!} </td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" role="button" id="dropdownMenuLink1"
                                                data-bs-toggle="dropdown" aria-expanded="false">
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
                                                <div class="col-12"><b>Historico:</b>
                                                    {{ $lancamento->historico ?? '-' }}</div>
                                                <div class="col-3"><b>Vencimento:</b>
                                                    {{ $lancamento->data_vencimento ? Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : '-' }}
                                                </div>
                                                <div class="col-3"><b>Documento:</b>
                                                    {{ $lancamento->documento ?? '-' }}</div>
                                                <div class="col-3"><b>Nº Documento:</b>
                                                    {{ $lancamento->num_documento ?? '-' }}</div>
                                                <div class="col-3"><b>Status:</b> {{ $lancamento->status ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center"> Não há lançamentos com vencimento nos
                                        próximos 7 dias, use o botão "Pesquisar" para atualizar a lista. </td>
                                </tr>
                            @endforelse
                            <tr>
                                <td colspan="5" class="border-0">
                                    <h6> Total da seleção: R$
                                        {{ $lancamentosfinanceiros->where('tipo_lancamento', 'CREDITO')->whereNotNull('data_pagamento')->sum('valor') }}
                                    </h6>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {{-- DESPESAS --}}
    <div class="col-6">
        <div class="card border-start border-danger border-4">
            <div class="card-header bg-danger-subtle">
                <h5>DESPESAS</h5>
            </div>
            <div class="card-body">

                <div class="table-responsive" style="min-height: 25vh">
                    <table class="table table-responsive border-1" style="table-layout: fixed">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 50%; white-space: nowrap;">Nome</th>
                                <th scope="col" style="white-space: nowrap;">Emissão</th>
                                <th scope="col" style="white-space: nowrap;">Valor</th>
                                <th scope="col" style="white-space: nowrap;">Pagamento</th>
                                <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lancamentosfinanceiros->where('tipo_lancamento', 'DEBITO') as $lancamento)
                                <tr>
                                    <td class="text-truncate">
                                        <a data-bs-toggle="collapse" href="{{ '#collapse' . $lancamento->uid }}"
                                            role="button" aria-expanded="false" aria-controls="collapseExample">
                                            <i class="ri-file-text-line btn-ghost ps-2 pe-3 fs-5"></i>
                                        </a> {{ $lancamento->pessoa->nome_razao }}
                                    </td>
                                    <td>{{ $lancamento->data_emissao ? Carbon\Carbon::parse($lancamento->data_emissao)->format('d/m/Y') : '-' }}
                                    </td>

                                    <td> <input type="text" class="money border-0 bg-transparent"
                                            value="{{ $lancamento->valor }}"> </td>
                                    <td> {!! $lancamento->data_pagamento
                                        ? Carbon\Carbon::parse($lancamento->data_pagamento)->format('d/m/Y')
                                        : "<span class='badge rounded-pill bg-warning'>Em Aberto</span>" !!} </td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" role="button" id="dropdownMenuLink1"
                                                data-bs-toggle="dropdown" aria-expanded="false">
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
                                                <div class="col-12"><b>Historico:</b>
                                                    {{ $lancamento->historico ?? '-' }}</div>
                                                <div class="col-3"><b>Vencimento:</b>
                                                    {{ $lancamento->data_vencimento ? Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : '-' }}
                                                </div>
                                                <div class="col-3"><b>Documento:</b>
                                                    {{ $lancamento->documento ?? '-' }}</div>
                                                <div class="col-3"><b>Nº Documento:</b>
                                                    {{ $lancamento->num_documento ?? '-' }}</div>
                                                <div class="col-3"><b>Status:</b> {{ $lancamento->status ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center"> Não há lançamentos com vencimento nos
                                        próximos 7 dias, use o botão "Pesquisar" para atualizar a lista. </td>
                                </tr>
                            @endforelse
                            <tr>
                                <td colspan="5" class="border-0">
                                    <h6> Total da seleção: R$
                                        {{ $lancamentosfinanceiros->where('tipo_lancamento', 'DEBITO')->sum('valor') }}
                                    </h6>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('mesAnoExport');
        const link = document.getElementById('linkExportLancamentos');

        select.addEventListener('change', function () {
            if (this.value) {
                const [mes, ano] = this.value.split('/');
                const routeTemplate = "{{ route('financeiro-export-mes', ['mes' => '__MES__', 'ano' => '__ANO__']) }}";
                const finalUrl = routeTemplate.replace('__MES__', mes).replace('__ANO__', ano);
                link.href = finalUrl;
                link.classList.remove('disabled');
            } else {
                link.href = "#";
                link.classList.add('disabled');
            }
        });
    });
</script>
