@php
    $sortDirection = request('order', 'asc') == 'asc' ? 'desc' : 'asc';
    $currentSortField = request('orderBy', 'nome');
    $searchTerm = request('buscanome', '');
@endphp
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="{{ route('interlab-insert') }}" class="btn btn-sm btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar
                </a>
            </div>
        </div>



        <div class="table-responsive" style="min-height: 25vh">
            <div>
                <input type="text" class="form-control form-control-sm" 
                    onkeypress="search(event, window.location.href, 'buscanome')"
                    placeholder="Buscar por nome" value="{{ $searchTerm }}"> 
            </div>
            <table class="table table-responsive table-striped align-middle mb-0" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th scope="col" class="d-none d-sm-table-cell" style="width: 10%; white-space: nowrap;">ID</th>
                        <th scope="col">
                            <a href="{{ request()->fullUrlWithQuery([
                                'orderBy' => 'nome', 
                                'order' => $sortDirection
                                ]) }}">
                                Nome
                                {!! $currentSortField == 'nome' 
                                    ? ($sortDirection == 'asc' 
                                        ? '<i class="ri-arrow-up-s-line"></i>' 
                                        : '<i class="ri-arrow-down-s-line"></i>') 
                                    : '' !!}
                            </a>
                        </th>
                        <th scope="col" style="width: 15%; white-space: nowrap;">
                            <a href="{{ request()->fullUrlWithQuery([
                                'orderBy' => 'avaliacao', 
                                'order' => $sortDirection
                                ]) }}">
                                Avaliação
                                {!! $currentSortField == 'avaliacao' 
                                    ? ($sortDirection == 'asc' 
                                        ? '<i class="ri-arrow-up-s-line"></i>' 
                                        : '<i class="ri-arrow-down-s-line"></i>') 
                                    : '' !!}
                            </a>
                        </th>
                        <th scope="col" style="width: 15%; white-space: nowrap;">
                            <a href="{{ request()->fullUrlWithQuery([
                                'orderBy' => 'tipo', 
                                'order' => $sortDirection
                                ]) }}">
                                Tipo
                                {!! $currentSortField == 'tipo' 
                                    ? ($sortDirection == 'asc' 
                                        ? '<i class="ri-arrow-up-s-line"></i>' 
                                        : '<i class="ri-arrow-down-s-line"></i>') 
                                    : '' !!}
                            </a>
                        </th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($interlabs as $interlab)
                        <tr>
                            <th scope="row" class="d-none d-sm-table-cell">
                                <a href="{{ route('interlab-insert', ['interlab' => $interlab->uid]) }}" class="fw-medium">
                                    #{{ substr($interlab->uid, 7) }}
                                </a>
                            </th>
                            <td>{{ $interlab->nome }}</td>
                            <td class="text-nowrap">{{ $interlab->avaliacao }}</td>
                            <td class="text-nowrap">{{ $interlab->tipo }}</td>
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
                                                href="{{ route('interlab-insert', ['interlab' => $interlab->uid]) }}">Editar</a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route="interlab-delete"
                                                id="{{ $interlab->uid }}" />
                                        </li>
                                    </ul>
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center"> Não há interlabs na base. </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="row mt-3 w-100">
                {!! $interlabs->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div>

    </div>
</div>
