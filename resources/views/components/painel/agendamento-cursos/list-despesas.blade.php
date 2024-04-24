@if( $errors->any() )
    <div class="alert alert-warning">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-12 d-flex justify-content-end my-3">
        <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#despesaModal">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar Despesa
        </a>
    </div>
</div>

<div class="table-responsive" style="min-height: 25vh">
    <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <th scope="col" style="width: 50%">Descricao</th>
                <th scope="col">Quantidade</th>
                <th scope="col">Val Unitário</th>
                <th scope="col">Val Total</th>
                <th scope="col" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
                @forelse ( $despesas as $despesa)
                <tr>
                    <td>{{ $despesa->materialPadrao->descricao }}</td>
                    <td>{{ $despesa->quantidade }}</td>
                    <td>{{ "R$ " . number_format($despesa->valor, 2, ',', '.') }}</td>
                    <td>{{ "R$ " . number_format($despesa->total, 2, ',', '.') }}</td>
                    <td>
                        <div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="{{ '#despesaModal' . $despesa->id }}">Editar</a>
                                </li>
                                <li>
                                    <x-painel.form-delete.delete route='materiais-padroes-delete' id="{{ $despesa->id }}" />
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <x-painel.agendamento-cursos.modal-despesa :despesa="$despesa" :agendacurso="$agendacurso" :materiaispadrao="$materiaispadrao"/>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Não há despesas cadastradas</td>
                    </tr>
                @endforelse
            
        </tbody>
    </table>

    <x-painel.agendamento-cursos.modal-despesa :agendacurso="$agendacurso" :materiaispadrao="$materiaispadrao"/>

</div>
