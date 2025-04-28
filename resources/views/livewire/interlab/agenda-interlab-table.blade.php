<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="{{ route('agenda-interlab-insert') }}" class="btn btn-sm btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar agenda interlab
                </a>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="card border shadow-sm mb-3">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="card-title mb-0">Filtros</h6>
                </div>

                <div class="row gx-3 align-items-center">
                    <!-- Status -->
                    <div class="col-2">
                        <label class="form-label mb-0">Status</label>
                        <select wire:model.live="status" class="form-select form-select-sm">
                            <option value="">Selecione...</option>
                            <option value="AGENDADO">AGENDADO</option>
                            <option value="CONFIRMADO">CONFIRMADO</option>
                            <option value="CONCLUIDO">CONCLUIDO</option>
                        </select>
                    </div>

                    <!-- Filtro por Empresa -->
                    <div class="col-3">
                        <label class="form-label mb-0">Empresa</label>
                        <select wire:model.live="empresaSelecionada" class="form-select form-select-sm">
                            <option value="">Selecione...</option>
                            @foreach ($this->empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->cpf_cnpj }} -
                                    {{ $empresa->nome_razao }}</option>
                            @endforeach
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
                                type="text" name="search" id="search"
                                placeholder="Pesquisar por nome do interlab ou empresa...">
                        </div>
                    </div>

                    <!-- Checkbox Inscrição s/ valor -->
                    <div class="col-2 d-flex flex-column align-items-center">
                        <label class="form-label mb-0 text-center">Inscrição s/ valor</label>
                        <div class="form-check">
                            <input wire:model.live="inscricaoSemValor" class="form-check-input" type="checkbox"
                                value="1" id="inscricaoSemValor">
                        </div>
                    </div>

                    <!-- Botão Limpar Filtros -->
                    <div class="col-2 text-end">
                        <div class="d-flex flex-column align-items-end">
                            <label class="form-label invisible mb-0">Limpar</label>
                            <button wire:click="resetFilters" type="button" class="btn btn-sm btn-light text-danger">
                                <i class="ri-close-line"></i> Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Filtros --}}

        {{-- Tabela --}}
        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-striped align-middle mb-0">
                <!-- Cabeçalho da tabela -->
                <thead>
                    <tr>
                        <th scope="col" style="width: 5%; white-space: nowrap;">Mês/Ano</th>
                        <th scope="col" style="width: 4%; white-space: nowrap;">COD</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;">
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
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                        <th scope="col" style="width: 5%; white-space: nowrap;">
                            <a href="#" wire:click.prevent="setSortBy('data_inicio')">
                                Data Inicio
                                @if ($sortBy == 'data_inicio')
                                    @if ($sortDirection == 'ASC')
                                        <i class="ri-arrow-up-s-line"></i>
                                    @else
                                        <i class="ri-arrow-down-s-line"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" style="width: 5%; white-space: nowrap;">
                            <a href="#" wire:click.prevent="setSortBy('data_fim')">
                                Data Fim
                                @if ($sortBy == 'data_fim')
                                    @if ($sortDirection == 'ASC')
                                        <i class="ri-arrow-up-s-line"></i>
                                    @else
                                        <i class="ri-arrow-down-s-line"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" style="width: 45%; white-space: nowrap;">
                            <a href="#" wire:click.prevent="setSortBy('nome')">
                                Nome do Interlab
                                @if ($sortBy == 'nome')
                                    @if ($sortDirection == 'ASC')
                                        <i class="ri-arrow-up-s-line"></i>
                                    @else
                                        <i class="ri-arrow-down-s-line"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" style="width: 5%; white-space: nowrap;">
                            <a href="#" wire:click.prevent="setSortBy('inscritos')">
                                Inscritos
                                @if ($sortBy == 'inscritos')
                                    @if ($sortDirection == 'ASC')
                                        <i class="ri-arrow-up-s-line"></i>
                                    @else
                                        <i class="ri-arrow-down-s-line"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agendainterlabs as $agendainterlab)
                        <tr>
                            <td class="text-uppercase">
                                {{ Carbon\Carbon::parse($agendainterlab->data_inicio)->format('m/Y') }}
                            </td>
                            <td class="text-center text-nowrap">
                                <a
                                    href="{{ route('agenda-interlab-insert', $agendainterlab->uid) }}">#{{ $agendainterlab->id }}</a>
                            </td>
                            <td
                                @if ($agendainterlab->status == 'CONFIRMADO') class="text-success fw-bold"
                                @elseif ($agendainterlab->status == 'AGENDADO') class="text-primary fw-bold"
                                @elseif ($agendainterlab->status == 'CONCLUIDO') class="text-danger fw-bold" @endif>
                                {{ $agendainterlab->status }}
                            </td>
                            <td style="white-space: nowrap;">
                                @if ($agendainterlab->site)
                                    <span data-bs-toggle="tooltip" data-bs-html="true" title="Visível no site">
                                        <i class="ri-terminal-window-line label-icon text-primary"
                                            style="font-size: 1.4rem"></i>
                                    </span>
                                @endif
                                @if ($agendainterlab->inscricao)
                                    &nbsp;
                                    <span data-bs-toggle="tooltip" data-bs-html="true" title="Inscrições abertas">
                                        <i class="ri-edit-2-fill label-icon text-success"
                                            style="font-size: 1.4rem"></i>
                                    </span>
                                @endif
                            </td>
                            <td>{{ $agendainterlab->data_inicio?->format('d/m/Y') }}</td>
                            <td>{{ $agendainterlab->data_fim?->format('d/m/Y') }}</td>
                            <td>{{ $agendainterlab->interlab->nome ?? '' }}</td>
                            <td class="text-end pe-2">{{ $agendainterlab->inscritos->count() }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Detalhes e edição">
                                        </i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li><a class="dropdown-item"
                                                href="{{ route('agenda-interlab-insert', $agendainterlab->uid) }}">Editar</a>
                                        </li>
                                        <li><x-painel.form-delete.delete route='agenda-interlab-delete'
                                                id="{{ $agendainterlab->uid }}" /></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Não há Agendamento de Interlabs</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="row mt-3 w-100">
                {{ $agendainterlabs->links() }}
            </div>
        </div>
    </div>
</div>
