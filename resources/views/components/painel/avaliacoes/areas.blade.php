<div class="row">
    <div class="col-12">
        <a href="#" class="btn btn-sm btn-success float-end" data-bs-toggle="modal" data-bs-target="#areaAvaliadaModal">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar Área
        </a>
    </div>
</div>
<div class="table-responsive mt-3" style="min-height: 180px">
    <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <th scope="col">Área</th>
                <th scope="col">Avaliador</th>
                <th scope="col">Situação</th>
                <th scope="col" class="d-sm-table-cell" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($avaliacao->areas as $areaavaliada)
                <tr>
                    <td class="text-truncate" style="max-width: 50vw">{{ $areaavaliada->areaAtuacao->descricao }}</td>
                    <td>{{ $areaavaliada->avaliador->pessoa->nome_razao }}</td>
                    <td>{{ $areaavaliada->situacao }}</td>
                    <td>
                        <div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="{{ '#areaAvaliadaModal'.$areaavaliada->uid }}">Editar</a>
                                </li>
                                <li>
                                    <x-painel.form-delete.delete route='laboratorio-delete-interno' id="{{ $areaavaliada->uid }}" />
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <x-painel.avaliacoes.modal-areas-avaliadas 
                    :laboratorio="$laboratorio" 
                    :avaliadores="$avaliadores" 
                    :avaliacao="$avaliacao" 
                    :areaavaliada="$areaavaliada" />
            @empty
                <tr>
                    <td colspan="5" class="text-center">Não há áreas de atuação cadastradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-painel.avaliacoes.modal-areas-avaliadas :laboratorio="$laboratorio" :avaliadores="$avaliadores" :avaliacao="$avaliacao" />
