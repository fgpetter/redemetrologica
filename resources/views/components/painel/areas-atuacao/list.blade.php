@php
    $sortDirection    = request('order', 'asc') == 'asc' ? 'desc' : 'asc';
    $currentSortField = request('orderBy', 'descricao');
    $searchTerm       = request('buscadecricao', '');
@endphp

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#areaAtuacaoModal">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Área de Atuação
                </a>
            </div>
        </div>
        <div class="mb-3">
            <input type="text" class="form-control form-control-sm" 
                   onkeypress="search(event, window.location.href, 'buscadecricao')"
                   placeholder="Buscar por descrição" 
                   value="{{ $searchTerm }}">
        </div>

        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="d-sm-table-cell" style="width: 5%; white-space: nowrap;">ID</th>
                        <th scope="col">
                            <a href="{{ request()->fullUrlWithQuery(['orderBy' => 'descricao', 'order' => $sortDirection]) }}">
                                Descrição {!! $currentSortField == 'descricao' ? ($sortDirection == 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>') : '' !!}
                            </a>
                        </th>
                        <th scope="col">Observações</th>
                        <th scope="col" class="d-sm-table-cell" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($areasAtuacao as $areaAtuacao)
                        <tr>
                            <th>
                                <a href="#" data-bs-toggle="modal"
                                   data-bs-target="{{ '#areaAtuacaoModal' . $areaAtuacao->uid }}">
                                    #{{ substr($areaAtuacao->uid, 7) }}
                                </a>
                            </th>
                            <td class="text-truncate" style="max-width: 50vw">{{ $areaAtuacao->descricao }}</td>
                            <td>{{ Str::of($areaAtuacao->observacoes)->limit(75) }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                           data-bs-toggle="tooltip" data-bs-placement="top"
                                           title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                               data-bs-target="{{ '#areaAtuacaoModal' . $areaAtuacao->uid }}">
                                                Editar
                                            </a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route="area-atuacao-delete" id="{{ $areaAtuacao->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <x-painel.areas-atuacao.modal-area-atuacao :area_atuacao="$areaAtuacao" />
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Não há áreas de atuação cadastradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="row mt-3 w-100">
                {!! $areasAtuacao->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div>
        <x-painel.areas-atuacao.modal-area-atuacao />
    </div>
</div>
