<div class="row">
    <div class="col-12">
        <a href="#" class="btn btn-sm btn-success float-end" data-bs-toggle="modal" data-bs-target="#avaliacaoModal">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar avaliacao
        </a>
    </div>
</div>
<div class="table-responsive" style="min-height: 25vh">
    <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <th scope="col" class=" d-sm-table-cell" style="width: 5%; white-space: nowrap;">ID
                </th>
                <th scope="col">Empresa</th>
                <th scope="col">Data</th>
                <th scope="col">Situação</th>
                <th scope="col" class=" d-sm-table-cell" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($avaliacoes as $avaliacao)
                <tr>
                    <th>
                        <a href="#" data-bs-toggle="modal"
                            data-bs-target="{{-- '#avaliacaoModal'.$avaliacao->uid --}}">#{{ substr($avaliacao->uid, 7) }} </a>
                    </th>
                    <td class="text-truncate" style="max-width: 50vw">{{ $avaliacao->empresa }}</td>
                    <td>{{ $avaliacao->data ? Carbon\Carbon::parse($avaliacao->data)->format('d/m/Y') : '' }}</td>
                    <td>{{ $avaliacao->situacao }}</td>
                    <td>
                        <div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="{{-- '#avaliacaoModal'.$avaliacao->uid --}}">Editar</a>
                                </li>
                                <li>

                                    <x-painel.form-delete.delete route='avaliador-delete' id="{{ $avaliacao->uid }}" />
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <x-painel.avaliadores.modal-avaliacoes-insert :avaliador="$avaliador" :avaliacao="$avaliacao" />
            @empty
                <tr>
                    <td colspan="4" class="text-center">Não há áreas de atuação cadastradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{-- <div class="row mt-3">
      {!! $areasAtuacao->withQueryString()->links('pagination::bootstrap-5') !!}
    </div> --}}
</div>
<x-painel.avaliadores.modal-avaliacoes-insert :avaliador="$avaliador" />
