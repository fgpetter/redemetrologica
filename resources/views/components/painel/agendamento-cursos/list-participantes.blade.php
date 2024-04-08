@if ($errors->any())
    <div class="alert alert-warning">
        <strong>Erro ao salvar os dados!</strong> <br><br>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- <div class="row">
    <div class="col-12">
        <a href="#" class="btn btn-sm btn-success float-end" data-bs-toggle="modal"
            data-bs-target="#inscritoModal">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar inscrito
        </a>
    </div>
</div> --}}


<div class="table-responsive" style="min-height: 25vh">
    <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <th scope="col" style="width: 5%; white-space: nowrap;">Data Inscrição</th>
                <th scope="col">Empresa</th>
                <th scope="col">Nome</th>
                <th scope="col" style="width: 5%; white-space: nowrap;">Confirmado</th>
                <th scope="col" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
            @if($inscritos)
                @foreach ($inscritos->sortBy('empresa_id') as $inscrito)
                    @if($inscrito->pessoa->tipo_pessoa === 'PF')
                        <tr>
                            <td>{{ Carbon\Carbon::parse($inscrito->data_inscricao)->format('d/m/Y') }}</td>
                            <td>{{ Str::limit($inscrito->empresa?->nome_razao, 30) ?? 'Individual' }}</td>
                            <td>{{ $inscrito->pessoa->nome_razao }}</td>
                            <td> {!! $inscrito->data_confirmacao 
                                    ? \Carbon\Carbon::parse($inscrito->data_confirmacao)->format('d/m/Y') 
                                    : '<span class="badge rounded-pill bg-warning">Não</span>' !!}
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="{{ '#inscritoModal' . $inscrito->uid }}">Editar</a>
                                        </li>
                                        <li>                                            
                                            <x-painel.form-delete.delete route='materiais-padroes-delete' id="{{ $inscrito->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <x-painel.agendamento-cursos.modal-participante :inscrito="$inscrito"/>
                    @endif
                @endforeach
            @else
            <tr>
                <td colspan="6" class="text-center">Este Instrutor não possui cursos realizados.</td>
            </tr>
            @endif
            
        </tbody>
    </table>

</div>
