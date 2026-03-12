<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="hstack gap-2 flex-wrap mb-3 justify-content-end">
                    <button class="btn btn-sm btn-success" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                        <i class="ri-add-line align-bottom me-1"></i> Adicionar Fornecedor
                    </button>
                </div>

                <div class="collapse" id="collapseExample">
                    <div class="card mb-3 shadow-none">
                        <div class="card-body">
                            <form action="{{ route('fornecedor-create') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-10">
                                        <select class="form-control" data-choices name="pessoa_uid" id="choices-single-default">
                                            <option value="">Selecione na lista</option>
                                            @foreach ($pessoas as $pessoa)
                                                <option value="{{ $pessoa->uid }}">{{ $pessoa->cpf_cnpj }} | {{ $pessoa->nome_razao }}</option>
                                            @endforeach
                                        </select>
                                        @error('pessoa_uid')<div class="text-warning">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-success" type="submit">Adicionar</button>
                                    </div>
                                </div>
                            </form>
                            <p>Caso a pessoa não esteja cadastrada ainda, <a href="{{ route('pessoa-insert') }}">Clique Aqui</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border shadow-sm mb-3">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="card-title mb-0">Filtros</h6>
                </div>

                <div class="row gx-3 align-items-center">
                    <div class="col-4">
                        <label class="form-label mb-0">Nome / Razão Social</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input wire:model.live.debounce.300ms="searchNome" class="form-control form-control-sm"
                                type="text" placeholder="Buscar...">
                        </div>
                    </div>

                    <div class="col-2">
                        <label class="form-label mb-0">CPF/CNPJ</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input wire:model.live.debounce.300ms="searchCpfCnpj" class="form-control form-control-sm"
                                type="text" placeholder="Buscar...">
                        </div>
                    </div>

                    <div class="col-2">
                        <label class="form-label mb-0">Área</label>
                        <select wire:model.live="area" class="form-select form-select-sm">
                            <option value="">Selecione...</option>
                            @foreach ($areasEnum as $areaCase)
                                <option value="{{ $areaCase->value }}">{{ $areaCase->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-1">
                        <label class="form-label mb-0 invisible">Limpar</label>
                        <button wire:click="resetFilters" type="button" class="btn btn-sm btn-light text-danger">
                            <i class="ri-close-line"></i> Limpar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 1%; white-space: nowrap;"></th>
                        <th scope="col" style="white-space: nowrap;">
                            <a href="" wire:click.prevent="setSortBy('nome_razao')">
                                Nome / Razão Social
                                @if ($sortBy === 'nome_razao')
                                    @if ($sortDirection === 'ASC')
                                        <i class="ri-arrow-up-s-line"></i>
                                    @else
                                        <i class="ri-arrow-down-s-line"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" style="white-space: nowrap;">
                            <a href="" wire:click.prevent="setSortBy('cpf_cnpj')">
                                CPF/CNPJ
                                @if ($sortBy === 'cpf_cnpj')
                                    @if ($sortDirection === 'ASC')
                                        <i class="ri-arrow-up-s-line"></i>
                                    @else
                                        <i class="ri-arrow-down-s-line"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col">Áreas</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($fornecedores as $fornecedor)
                        <tr wire:key="fornecedor-{{ $fornecedor->id }}">
                            <td style="width: 1%; white-space: nowrap;">
                                <a data-bs-toggle="collapse" href="{{ '#collapse' . $fornecedor->uid }}" role="button"
                                    aria-expanded="false" aria-controls="collapseExample">
                                    <i class="ri-file-text-line btn-ghost ps-2 pe-3 fs-5" style="color: #0d6efd;"></i>
                                </a>
                            </td>
                            <td>{{ $fornecedor->pessoa?->nome_razao }}</td>
                            <td>{{ $fornecedor->pessoa?->cpf_cnpj }}</td>
                            <td>
                                {{ $fornecedor->areas->map(fn ($a) => $a->area->label())->implode(' | ') ?: '-' }}
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink-{{ $fornecedor->uid }}"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink-{{ $fornecedor->uid }}">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('fornecedor-insert', ['fornecedor' => $fornecedor->uid]) }}">Editar</a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route="fornecedor-delete"
                                                id="{{ $fornecedor->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <tr wire:key="fornecedor-detail-{{ $fornecedor->id }}">
                            <td colspan="5" class="p-0">
                                <div class="collapse" id="{{ 'collapse' . $fornecedor->uid }}">
                                    <div class="row m-3 pe-2">
                                        @forelse ($fornecedor->areas as $area)
                                            <div class="col-12 mb-3">
                                                <b>Contato para {{ $area->area->label() }}: &nbsp;</b>
                                                    {{ $area->pessoa_contato }} - 
                                                    {{ $area->pessoa_contato_email }} - 
                                                    {{ $area->pessoa_contato_telefone }}
                                            </div>
                                        @empty
                                            <div class="col-12 text-muted">Nenhuma área cadastrada</div>
                                        @endforelse
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Não há fornecedores na base.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="row mt-3 w-100 justify-content-between align-items-center">
                <div class="d-flex mb-3 align-items-center gap-2" style="max-width: 210px;">
                    <label class="form-label mb-0 text-muted">Itens por página:</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 70px;">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <div class="col">
                    {{ $fornecedores->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
