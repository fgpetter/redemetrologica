<div class="row">
    <div class="col-12">
        <a href="#" class="btn btn-sm btn-success float-end" data-bs-toggle="modal"
            data-bs-target="#qualificacaoModal">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar qualificação
        </a>
    </div>
</div>
<div class="table-responsive" style="min-height: 25vh">
    <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <th scope="col" class=" d-sm-table-cell" style="width: 5%; white-space: nowrap;">ID
                </th>
                <th scope="col">Ano</th>
                <th scope="col">Atividade</th>
                <th scope="col">Instrutor</th>
                <th scope="col" class=" d-sm-table-cell" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($qualificacoes as $qualificacao)
                <tr>
                    <th>
                        <a href="#" data-bs-toggle="modal"
                            data-bs-target="{{ '#qualificacaoModal' . $qualificacao->uid }}">#{{ substr($qualificacao->uid, 7) }}
                        </a>
                    </th>
                    <td>{{ $qualificacao->ano }}</td>
                    <td>{{ $qualificacao->atividade }}</td>
                    <td>{{ $qualificacao->instrutor }}</td>
                    <td>
                        <div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="{{ '#qualificacaoModal' . $qualificacao->uid }}">Editar</a>
                                </li>
                                <li>

                                    <x-painel.form-delete.delete route='avaliador-delete-qualificacao'
                                        id="{{ $qualificacao->uid }}" />
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <x-painel.avaliadores.modal-qualificacoes-insert :qualificacao="$qualificacao" :qualificacoeslist="$qualificacoeslist" :avaliador="$avaliador"/>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Não há qualificações cadastradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-painel.avaliadores.modal-qualificacoes-insert :qualificacoeslist="$qualificacoeslist" :avaliador="$avaliador"/>
