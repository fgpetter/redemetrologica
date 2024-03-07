<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#parametroModal">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Parâmetro
                </a>
            </div>
        </div>

            <x-alerts.alert  />

        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th scope="col" class=" d-sm-table-cell" style="width: 5%; white-space: nowrap;">ID
                        </th>
                        <th scope="col">Descrição</th>
                        <th scope="col" class=" d-sm-table-cell" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($parametros as $parametro)
                        <tr>
                            <th><a href="#" class="fw-medium" data-bs-toggle="modal"
                                    data-bs-target="{{ '#materialModal' . $parametro->uid }}">
                                    #{{ substr($parametro->uid, 7) }}
                                </a></th>
                            <td class="text-truncate" style="max-width: 50vw">{{ $parametro['descricao'] }}</td>
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
                                                data-bs-target="{{ '#materialModal' . $parametro->uid }}">Editar</a>
                                        </li>
                                        <li>

                                            <x-painel.form-delete.delete route='parametro-delete'
                                                id="{{ $parametro->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <x-painel.parametros.modal-parametros :parametro="$parametro" />
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Não há parâmetros cadastrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- <div class="row mt-3">
    {!! $padroes->withQueryString()->links('pagination::bootstrap-5') !!}
    </div> --}}
        </div>
        <x-painel.parametros.modal-parametros />
    </div>
</div>
