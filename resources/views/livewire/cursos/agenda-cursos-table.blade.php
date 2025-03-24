@php
    $order = request('order', 'asc') == 'asc' ? 'desc' : 'asc';
    $currentOrderBy = request('orderBy', 'data_inicio');
    $busca_nome = request('buscanome', '');
@endphp
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="{{ $tipoagenda == 'IN-COMPANY' ? route('agendamento-curso-in-company-insert') : route('agendamento-curso-insert') }}"
                    class="btn btn-sm btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Agendamento de curso
                </a>
            </div>
        </div>
        {{-- debug
        <div class="text-muted small">
            Selecionados: {{ json_encode($selectedRows) }}
        </div> --}}
        {{-- Filtros --}}
        <div class="card border shadow-sm mb-3">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="card-title text-muted mb-0 ">Filtros</h6>
                </div>

                <div class="row g-2">
                    <!-- Status -->
                    <div class="col-3">
                        <select wire:model.live="status" name="filtro" class="form-select form-select-sm">
                            <option value="">Status</option>
                            <option value="AGENDADO">AGENDADO</option>
                            <option value="CANCELADO">CANCELADO</option>
                            <option value="CONFIRMADO">CONFIRMADO</option>
                            <option value="REALIZADO">REALIZADO</option>
                            <option value="REAGENDAR">REAGENDAR</option>
                        </select>
                    </div>

                    <!-- Tipo -->
                    <div class="col-3">
                        <select wire:model.live="tipo_agendamento" name="tipoAgendamento"
                            class="form-select form-select-sm">
                            @if ($tipoagenda == 'IN-COMPANY')
                                <option value="IN-COMPANY">IN-COMPANY</option>
                            @else
                                <option value="">Tipo</option>
                                <option value="ONLINE">ONLINE</option>
                                <option value="EVENTO">EVENTO</option>
                            @endif
                        </select>
                    </div>

                    <div class=" col-1 d-flex align-items-center">
                        <div class="vr  mx-auto"></div>
                    </div>


                    <!-- Datas -->
                    <div class="col-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-0 px-1">Data:</span>
                            <input wire:model.live="dataIni" type="date" name="dataInicial"
                                class="form-control form-control-sm" placeholder="Data Inicial">
                            <span class="input-group-text bg-transparent border-0 px-1">até</span>
                            <input wire:model.live="dataFim" type="date" name="dataFinal"
                                class="form-control form-control-sm" placeholder="Data Final">
                        </div>
                    </div>
                    <div class="col-2 text-end ">
                        <button wire:click="resetFilters" type="button" class="btn btn-sm btn-light text-danger"
                            onclick="clearFilters()">
                            <i class="ri-close-line"></i> Limpar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Filtros --}}

        <div class="table-responsive" style="min-height: 25vh">


            <!-- Totalizador -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <!-- Totalizador -->
                <div>
                    <strong>Total:</strong> R$ {{ number_format($totalValor, 2, ',', '.') }}
                </div>
                <!-- pesquisa global -->
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="ri-search-line"></i>
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="text"
                            class="form-control form-control-sm" placeholder="Pesquisar...">
                    </div>
                </div>
            </div>


            <table class="table table-responsive table-striped align-middle mb-0">
                <!-- Cabeçalho ordenável -->
                <thead>
                    <tr>
                        <th></th>
                        <th scope="col" style="width: 5%; white-space: nowrap;">Mês</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;">ID</th>
                        <!-- Status -->
                        <th scope="col" style="width: 7%; white-space: nowrap;">
                            <a href="#" wire:click.prevent="setSortBy('status')">
                                Status
                                @if ($sortBy == 'status')
                                    @if ($sortDirection == 'ASC')
                                        <i class="ri-arrow-up-s-line"></i>
                                    @else
                                        <i class="ri-arrow-down-s-line"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col"></th>
                        <!-- Data Início -->
                        <th scope="col" style="width: 7%; white-space: nowrap;">
                            <a href="#" wire:click.prevent="setSortBy('data_inicio')">
                                Data Início
                                @if ($sortBy == 'data_inicio')
                                    @if ($sortDirection == 'ASC')
                                        <i class="ri-arrow-up-s-line"></i>
                                    @else
                                        <i class="ri-arrow-down-s-line"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <!-- Nome do Curso -->
                        <th scope="col">
                            <a href="#" wire:click.prevent="setSortBy('curso')">
                                Nome do Curso
                                @if ($sortBy == 'curso')
                                    @if ($sortDirection == 'ASC')
                                        <i class="ri-arrow-up-s-line"></i>
                                    @else
                                        <i class="ri-arrow-down-s-line"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <!-- Tipo -->
                        <th scope="col" style="width: 10%; white-space: nowrap;">
                            <a href="#" wire:click.prevent="setSortBy('tipo_agendamento')">
                                Tipo
                                @if ($sortBy == 'tipo_agendamento')
                                    @if ($sortDirection == 'ASC')
                                        <i class="ri-arrow-up-s-line"></i>
                                    @else
                                        <i class="ri-arrow-down-s-line"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="text-center">Inscritos</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($agendacursos as $agendacurso)
                        <tr wire:key="{{ $agendacurso->id }}">
                            <td>
                                <input wire:model.live="selectedRows" type="checkbox" value="{{ $agendacurso->id }}">
                            </td>
                            <td class="text-uppercase ">
                                {{ Carbon\Carbon::parse($agendacurso->data_inicio)->locale('pt-BR')->translatedFormat('F') }}
                            </td>
                            <td class="text-center text-nowrap">
                                <a
                                    href="{{ $tipoagenda == 'IN-COMPANY'
                                        ? route('agendamento-curso-in-company-insert', $agendacurso->uid)
                                        : route('agendamento-curso-insert', $agendacurso->uid) }}">
                                    # {{ $agendacurso->id }}</a>
                            </td>
                            <td
                                class="small text-nowrap align-middle p-1
                                @switch($agendacurso->status)
                                    @case('CONFIRMADO') text-success  @break
                                    @case('REAGENDAR') text-primary  @break
                                    @case('CANCELADO') text-danger  @break
                                @endswitch">
                                {{ $agendacurso->status }}
                            </td>

                            <td class="text-center text-nowrap align-middle">
                                @if ($agendacurso->site)
                                    <span class="me-1" data-bs-toggle="tooltip" title="Visível no site">
                                        <i class="ri-terminal-window-line text-primary" style="font-size: 1.1rem"></i>
                                    </span>
                                @endif
                                @if ($agendacurso->inscricoes)
                                    <span data-bs-toggle="tooltip" title="Inscrições abertas">
                                        <i class="ri-edit-2-fill text-success" style="font-size: 1.1rem"></i>
                                    </span>
                                @endif
                            </td>
                            <td class="text-center small text-nowrap mx-2">
                                {{ \Carbon\Carbon::parse($agendacurso->data_inicio)->format('d/m/Y') }}</td>
                            <td class="small  align-middle p-1">{{ $agendacurso->curso->descricao ?? '' }}</td>
                            <td class="small align-middle p-1">{{ $agendacurso->tipo_agendamento ?? '' }}</td>
                            <td class="text-center small text-nowrap align-middle p-1">
                                {{ $agendacurso->tipo_agendamento != 'IN-COMPANY'
                                    ? $agendacurso->inscritos->where('valor', '!=', null)->count()
                                    : $agendacurso->inscritos->count() }}
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li><a class="dropdown-item"
                                                href="{{ $tipoagenda == 'IN-COMPANY'
                                                    ? route('agendamento-curso-in-company-insert', $agendacurso->uid)
                                                    : route('agendamento-curso-insert', $agendacurso->uid) }}">
                                                Editar</a>
                                        </li>
                                        <li>

                                            <x-painel.form-delete.delete route='agendamento-curso-delete'
                                                id="{{ $agendacurso->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Não há Agendamento de Cursos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Paginação -->
            <div class="d-inline-flex align-items-center gap-1 mt-3">
                <label class="form-label mb-0 small text-muted me-1">Itens por página:</label>
                <select wire:model.live='perPage' class="form-select form-select-sm w-auto py-0"
                    style="width: 70px;">
                    <option value="15">15</option>˝
                    <option value="30">30</option>
                    <option value="50">50</option>
                </select>
            </div>

            <div class="row mt-1 w-100">
                {{ $agendacursos->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
