<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <div class="d-grid gap-2" style="width: fit-content;">
                    <a href="{{  route('agendamento-curso-insert') }}"
                        class="btn btn-sm btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Adicionar Agendamento de curso
                    </a>
                    <a href="{{ route('agendamento-curso-in-company-insert')  }}"
                        class="btn btn-sm btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Adicionar Agendamento de IN-COMPANY
                    </a>
                </div>
            </div>
        </div>
        </div>
        {{-- Filtros --}}
        <div class="card border shadow-sm mb-3">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="card-title mb-0">Filtros</h6>
                </div>

                <div class="row gx-3 align-items-center">
                    <!-- Data Inicial -->
                    <div class="col-2">
                        <label class="form-label mb-0">Data Inicial</label>
                        <input wire:model.live="dataIni" class="form-control form-control-sm" type="date"
                            name="data_inicial" id="data_inicial">
                    </div>

                    <!-- Data Final -->
                    <div class="col-2">
                        <label class="form-label mb-0">Data Final</label>
                        <input wire:model.live="dataFim" class="form-control form-control-sm" type="date"
                            name="data_final" id="data_final">
                    </div>

                    <!-- Status -->
                    <div class="col-2">
                        <label class="form-label mb-0">Status</label>
                        <select wire:model.live="status" class="form-select form-select-sm">
                            <option value="">Selecione...</option>
                            <option value="AGENDADO">AGENDADO</option>
                            <option value="CANCELADO">CANCELADO</option>
                            <option value="CONFIRMADO">CONFIRMADO</option>
                            <option value="REALIZADO">REALIZADO</option>
                            <option value="REAGENDAR">REAGENDAR</option>
                        </select>
                    </div>
                    <!-- Tipo -->
                        <div class="col-2">
                            <label class="form-label mb-0">Tipo</label>
                            <select wire:model.live="tipo_agendamento" name="tipoAgendamento" id="tipoAgendamento" class="form-select form-select-sm">
                                <option value="">Selecione...</option>
                                <option value="ONLINE">ONLINE</option>
                                <option value="EVENTO">EVENTO</option>
                                <option value="IN-COMPANY">IN-COMPANY</option>
                            </select>
                        </div>
                    

                    <!-- Pesquisa Global -->
                    <div class="col-3">
                        <label class="form-label mb-0">Pesquisar</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="ri-search-line"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                                type="text" name="search" id="search" placeholder="Pesquisar por nome do curso...">
                        </div>
                    </div>

                    <!-- Botão Limpar Filtros -->
                    <div class="col-1">
                        <!-- Label oculto para manter o alinhamento -->
                        <label class="form-label mb-0 invisible">Limpar</label>
                        <button wire:click="resetFilters" type="button" class="btn btn-sm btn-light text-danger">
                            <i class="ri-close-line"></i> Limpar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Filtros --}}
        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle mb-0">
                <!-- Cabeçalho ordenável -->
                <thead>
                    <tr>
                        <th scope="col" style="width: 5%; white-space: nowrap;">Mês</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;">ID</th>
                        <!-- Status -->
                        <th scope="col" style="width: 7%; white-space: nowrap;">
                            <a href="" wire:click.prevent="setSortBy('status')">
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
                            <a href="" wire:click.prevent="setSortBy('data_inicio')">
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
                            <a href="" wire:click.prevent="setSortBy('curso')">
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
                            <a href="" wire:click.prevent="setSortBy('tipo_agendamento')">
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
                            <td class="text-uppercase fw-bold">
                                {{ Carbon\Carbon::parse($agendacurso->data_inicio)->locale('pt-BR')->translatedFormat('F') }}
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ $agendacurso->tipo_agendamento == 'IN-COMPANY'
                                    ? route('agendamento-curso-in-company-insert', $agendacurso->uid)
                                    : route('agendamento-curso-insert', $agendacurso->uid) }}">
                                    # {{ $agendacurso->id }}
                                </a>
                            </td>
                            <td class="text-nowrap align-middle p-1
                                @switch($agendacurso->status)
                                    @case('CONFIRMADO') text-success fw-bold @break
                                    @case('REAGENDAR') text-primary fw-bold @break
                                    @case('CANCELADO') text-danger fw-bold @break
                                @endswitch" >
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
                            <td class="text-center text-nowrap mx-2">
                                {{ \Carbon\Carbon::parse($agendacurso->data_inicio)->format('d/m/Y') }}</td>
                            <td class="align-middle p-1">{{ $agendacurso->curso->descricao ?? '' }}</td>
                            <td class="align-middle p-1">{{ $agendacurso->tipo_agendamento ?? '' }}</td>
                            <td class="text-center text-nowrap align-middle p-1">
                                {{ $agendacurso->tipo_agendamento != 'IN-COMPANY'
                                    ? $agendacurso->inscritos_validos_count
                                    : $agendacurso->inscritos_count }}
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
                                        <li>
                                            <a class="dropdown-item" href="{{ $agendacurso->tipo_agendamento == 'IN-COMPANY'
                                                ? route('agendamento-curso-in-company-insert', $agendacurso->uid)
                                                : route('agendamento-curso-insert', $agendacurso->uid) }}">
                                                Editar
                                            </a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route='agendamento-curso-delete' id="{{ $agendacurso->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Não há Agendamento de Cursos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="row mt-3 w-100 justify-content-between align-items-center">
                <div class="d-flex mb-3 align-items-center gap-2" style="max-width: 210px;">
                    <!-- Itens por página -->
                    <label class="form-label mb-0 text-muted">Itens por página:</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 70px;">
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <div class="col">
                    {{ $agendacursos->links() }}
                </div>
            </div>

        </div>
    </div>
</div>
