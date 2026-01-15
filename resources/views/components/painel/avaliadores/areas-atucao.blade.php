<div class="row mt-4">
    <div class="col-9">
        <h5 class="card-title ps-1">Áreas de Atuação</h5>
    </div>
    <div class="col-3">
        <a href="#" class="btn btn-sm btn-success float-end" data-bs-toggle="modal"
            data-bs-target="#areaModal">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar área
        </a>
    </div>
</div>
<div class="table-responsive" style="min-height: 180px">
    <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <th scope="col" class=" d-sm-table-cell" style="width: 5%; white-space: nowrap;">ID
                </th>
                <th scope="col">Data Cadastro</th>
                <th scope="col">Situação</th>
                <th scope="col">Área de atuação</th>
                <th scope="col" class=" d-sm-table-cell" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($avaliador->areas as $area)
                <tr>
                    <th>
                        <a href="#" data-bs-toggle="modal"
                            data-bs-target="{{ '#areaModal' . $area->uid }}">#{{ substr($area->uid, 7) }}
                        </a>
                    </th>
                    <td>{{ $area->data_cadastro }}</td>
                    <td>{{ $area->situacao }}</td>
                    <td>{{ $area->area->descricao }}</td>
                    <td>
                        <div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="{{ '#areaModal' . $area->uid }}">Editar</a>
                                </li>
                                <li>

                                    <x-painel.form-delete.delete route='avaliador-delete-area'
                                        id="{{ $area->uid }}" />
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <x-painel.avaliadores.modal-areas-insert :area="$area" :avaliador="$avaliador" :areasatuacao="$areasatuacao"/>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Não há qualificações cadastradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-painel.avaliadores.modal-areas-insert :avaliador="$avaliador" :areasatuacao="$areasatuacao"/>
