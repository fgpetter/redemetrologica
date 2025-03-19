@php
    $sortDirection = request('order', 'asc') == 'asc' ? 'desc' : 'asc';
    $currentSortField = request('orderBy', 'descricao');
    $searchTerm = request('buscadecricao', '');
@endphp

<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-end">
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#materialModal">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Material
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
                        <th scope="col" style="width: 5%; white-space: nowrap;">ID</th>
                        <th scope="col">
                            <a href="{{ request()->fullUrlWithQuery(['orderBy' => 'descricao', 'order' => $sortDirection]) }}">
                                Descrição {!! $currentSortField === 'descricao' ? ($sortDirection === 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>') : '' !!}
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ request()->fullUrlWithQuery(['orderBy' => 'tipo', 'order' => $sortDirection]) }}">
                                Tipo {!! $currentSortField === 'tipo' ? ($sortDirection === 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>') : '' !!}
                            </a>
                        </th>
                        <th scope="col" style="width: 5%; white-space: nowrap;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materiais as $material)
                        <tr>
                            <th>
                                <a href="#" class="fw-medium" data-bs-toggle="modal" data-bs-target="{{ '#materialModal' . $material->uid }}">
                                    #{{ substr($material->uid, 7) }}
                                </a>
                            </th>
                            <td>{{ $material->descricao }}</td>
                            <td>{{ $material->tipo }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#"
                                               data-bs-toggle="modal"
                                               data-bs-target="{{ '#materialModal' . $material->uid }}">
                                                Editar
                                            </a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route="materiais-padroes-delete" id="{{ $material->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <x-painel.materiais-padroes.modal-materiais-padroes :material="$material" />
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Não há materiais cadastrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3 w-100">
                {!! $materiais->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div>
        <x-painel.materiais-padroes.modal-materiais-padroes />
    </div>
</div>
