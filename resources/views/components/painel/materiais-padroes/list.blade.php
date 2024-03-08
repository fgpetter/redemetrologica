<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#materialModal">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Material
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
                        <th scope="col" style="width:40%">Fornecedor</th>
                        <th scope="col">Valor</th>
                        <th scope="col">Padrao</th>
                        <th scope="col" style="width: 5%"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materiais as $material)
                        <tr>
                            <th><a href="#" class="fw-medium" data-bs-toggle="modal"
                                    data-bs-target="{{ '#materialModal' . $material->uid }}">
                                    #{{ substr($material->uid, 7) }}
                                </a></th>
                            <td>{{ $material->descricao }}</td>
                            <td>{{ $material->fornecedor }}</td>
                            <td>R$ <input type="text" class="money" value="{{ $material['valor'] }}"
                                    style="border: 0; background: transparent" size="9"></td>
                            <td>
                                @if ($material->padrao)
                                    <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i>
                                @endif
                            </td>
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
                                                data-bs-target="{{ '#materialModal' . $material->uid }}">Editar</a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route='materiais-padroes-delete'
                                                id="{{ $material->uid }}" />
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
            <div class="row mt-3">
                {!! $materiais->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div>
        <x-painel.materiais-padroes.modal-materiais-padroes />

    </div>
</div>
