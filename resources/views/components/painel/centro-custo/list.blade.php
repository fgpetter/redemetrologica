<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#centro-custoModal">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Centro de Custo
                </a>
            </div>
        </div>




        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 5%; white-space: nowrap;">ID
                        </th>
                        <th scope="col">Descrição</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($centrocustos as $centrocusto)
                        <tr>
                            <th><a href="#" class="fw-medium" data-bs-toggle="modal"
                                    data-bs-target="{{ '#centro-custoModal' . $centrocusto->uid }}">
                                    #{{ substr($centrocusto->uid, 7) }}
                                </a></th>
                            <td class="text-truncate" style="max-width: 50vw">{{ $centrocusto->descricao }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                data-bs-target="{{ '#centro-custoModal' . $centrocusto->uid }}">Editar</a>
                                        </li>
                                        <li>

                                            <x-painel.form-delete.delete route='centro-custo-delete'
                                                id="{{ $centrocusto->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <x-painel.centro-custo.modal-centro-custo :centrocusto="$centrocusto" />
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Não há centro de custo cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- <div class="row mt-3 w-100">
            {!! $padroes->withQueryString()->links('pagination::bootstrap-5') !!}
            </div> --}}
        </div>
        <x-painel.centro-custo.modal-centro-custo />
    </div>
</div>
