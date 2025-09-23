<div class="row">
    <div class="col-12">
        <a href="#" class="btn btn-sm btn-success float-end" data-bs-toggle="modal" data-bs-target="#labinternoModal">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar laboratório
        </a>
    </div>
</div>
<div class="table-responsive mt-3" style="min-height: 25vh">
    <table class="table table-responsive table-striped align-middle  mb-0">
        <thead>
            <tr>
                <th scope="col">Área</th>
                <th scope="col">Nome</th>
                <th scope="col" style="width: 5%; white-space: nowrap;">Reconhechecido</th>
                <th scope="col" style="width: 5%; white-space: nowrap;">Cert. Site</th>
                <th scope="col" style="width: 5%; white-space: nowrap;">Última avaliação</th>
                <th scope="col" class="d-sm-table-cell" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laboratorio->laboratoriosInternos as $lab_interno)
                <tr>
                    <td class="text-truncate" style="max-width: 50vw">{{ Str::limit($lab_interno->area->descricao , 60, '...')  }}</td>
                    <td>{{ $lab_interno->nome }}</td>
                    <td>{!! ( $lab_interno->reconhecido )
                        ? '<span class="badge rounded-pill bg-success">Sim</span>'
                        : '<span class="badge rounded-pill bg-danger">Não</span>' !!}</td>
                    <td>{!! ( $lab_interno->site )
                        ? '<span class="badge rounded-pill bg-success">Sim</span>'
                        : '<span class="badge rounded-pill bg-danger">Não</span>' !!}</td>
                    <td>{{ now()->format('d/m/Y') }}</td>
                    <td>
                        <div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="{{ '#labinternoModal'.$lab_interno->uid }}">Editar</a>
                                </li>
                                <li>
                                    <x-painel.form-delete.delete route='laboratorio-delete-interno' id="{{ $lab_interno->uid }}" />
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <x-painel.laboratorios.modal-lab-internos :laboratorio="$laboratorio" :labinterno="$lab_interno" :areasatuacao="$areasatuacao"/>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Não há áreas de atuação cadastradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{-- <div class="row mt-3 w-100">
      {!! $areasAtuacao->withQueryString()->links('pagination::bootstrap-5') !!}
    </div> --}}
</div>
<x-painel.laboratorios.modal-lab-internos :laboratorio="$laboratorio" :areasatuacao="$areasatuacao" />
