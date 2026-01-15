<div class="row mt-4">
    <div class="col-12">
        <a href="#" class="btn btn-sm btn-success float-end" data-bs-toggle="modal"
            data-bs-target="#certificadoModal">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar certificado
        </a>
    </div>
</div>
<div class="table-responsive" style="min-height: 180px">
    <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <th scope="col" class=" d-sm-table-cell" style="width: 5%; white-space: nowrap;">ID
                </th>
                <th scope="col">Data</th>
                <th scope="col">Revisão</th>
                <th scope="col">Responsável</th>
                <th scope="col">Motivo</th>
                <th scope="col" class=" d-sm-table-cell" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($avaliador->certificados as $certificado)
                <tr>
                    <th>
                        <a href="#" data-bs-toggle="modal"
                            data-bs-target="{{ '#certificadoModal' . $certificado->uid }}">#{{ substr($certificado->uid, 7) }}
                        </a>
                    </th>
                    <td>{{ $certificado->data }}</td>
                    <td>{{ $certificado->revisao }}</td>
                    <td>{{ $certificado->responsavel }}</td>
                    <td>{{ $certificado->motivo }}</td>
                    <td>
                        <div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" 
                                        data-bs-target="{{ '#certificadoModal' . $certificado->uid }}">Editar</a>
                                </li>
                                <li>
                                    <x-painel.form-delete.delete route='avaliador-delete-certificado' id="{{ $certificado->uid }}" />
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <x-painel.avaliadores.modal-certificados-insert :certificado="$certificado" :avaliador="$avaliador"/>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Não há qualificações cadastradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-painel.avaliadores.modal-certificados-insert :avaliador="$avaliador"/>
