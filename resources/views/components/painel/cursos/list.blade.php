<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="{{ route('curso-insert') }}" class="btn btn-sm btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar
                </a>
            </div>
        </div>



        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0"
                style="table-layout: fixed">
                <thead>
                    <tr>
                        <th scope="col" class="d-none d-sm-table-cell" style="width: 10%; white-space: nowrap;">ID
                        </th>
                        <th scope="col" style="width: 50%; white-space: nowrap;">Descricao</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Carga Horária</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cursos as $curso)
                        <tr>
                            <th scope="row" class="d-none d-sm-table-cell">
                                <a href="{{ route('curso-insert', ['curso' => $curso->uid]) }}" class="fw-medium">
                                    #{{ substr($curso->uid, 7) }}
                                </a>
                            </th>
                            <td class="text-truncate">{{ $curso->descricao }}</td>
                            <td >{{ $curso->tipo_curso }}</td>
                            <td >{{ $curso->carga_horaria }}</td>
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
                                                href="{{ route('curso-insert', ['curso' => $curso->uid]) }}">Editar</a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route="curso-delete"
                                                id="{{ $curso->uid }}" />
                                        </li>
                                    </ul>
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center"> Não há cursos na base. </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="row mt-3">
                {!! $cursos->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div>

    </div>
</div>
