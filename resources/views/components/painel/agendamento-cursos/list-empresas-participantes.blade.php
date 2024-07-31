<div class="table-responsive" style="min-height: 180px">
    <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <th scope="col">Empresa</th>
                <th scope="col">Participantes</th>
                <th scope="col">Associado</th>
                <th scope="col" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inscritos as $inscrito)
                @if($inscrito->pessoa->tipo_pessoa === 'PJ')
                    <tr>
                        <td>{{ $inscrito->pessoa->nome_razao }}</td>
                        <td>{{ $inscritos->where('empresa_id', $inscrito->pessoa_id)->count() }}</td>
                        <td>{!! ($inscrito->pessoa->associado) 
                            ? '<span class="badge rounded-pill bg-success">Sim</span>' 
                            : '<span class="badge rounded-pill bg-warning">Não</span>' !!}</td>
                        <td>
                            <div class="dropdown">
                                <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                            data-bs-target="#participanteModal">Editar</a>
                                    </li>
                                    <li>

                                        <x-painel.form-delete.delete route='materiais-padroes-delete'
                                            id="{{ $inscrito->uid }}" />
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <x-painel.agendamento-cursos.modal-participante />
                @endif
            @empty
                <tr>
                    <td colspan="6" class="text-center">Este agendamento não possui inscritos.</td>
                </tr>
            @endforelse
            
        </tbody>
    </table>

</div>
