<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#planocontaModal">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Plano de Conta
                </a>
            </div>
        </div>


        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 5%">ID
                        </th>
                        <th scope="col" style="width:40%">Descrição</th>
                        <th scope="col">COD Contábil</th>
                        <th scope="col">Grupo de contas</th>
                        <th scope="col">Centro de custo</th>
                        <th scope="col" style="width: 5%"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($planocontas as $planoconta)
                        <tr>
                            <th><a href="#" class="fw-medium" data-bs-toggle="modal"
                                    data-bs-target="{{ '#planocontaModal' . $planoconta->uid }}">
                                    #{{ substr($planoconta->uid, 7) }}
                                </a></th>
                            <td>{{ $planoconta->descricao }}</td>
                            <td>{{ $planoconta->codigo_contabil }}</td>
                            <td>{{ $planoconta->grupo_contas }}</td>
                            <td>{{ $planoconta->centrocusto->descricao ?? null }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                data-bs-target="{{ '#planocontaModal' . $planoconta->uid }}">Editar</a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route='plano-conta-delete'
                                                id="{{ $planoconta->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <x-painel.plano-conta.modal-plano-conta :planoconta="$planoconta" :centrocustos="$centrocustos" />

                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Não há plano de contas cadastrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{!! $planocontas->withQueryString()->links('pagination::bootstrap-5') !!}</div>
        </div>
        <x-painel.plano-conta.modal-plano-conta :centrocustos="$centrocustos" />

    </div>
</div>
