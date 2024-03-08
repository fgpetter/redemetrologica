@props([
    'inscritos' => [],
])
<div class="row">
    <div class="col-12">
        <a href="#" class="btn btn-sm btn-success float-end" data-bs-toggle="modal"
            data-bs-target="#participanteModal">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar participante
        </a>
    </div>
</div>


<div class="table-responsive" style="min-height: 25vh">
    <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <th scope="col">Data Inscrição</th>
                <th scope="col">Empresa</th>
                <th scope="col">Nome</th>
                <th scope="col">Confirmou</th>
                <th scope="col" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inscritos as $inscrito)
                <tr>
                    <td>{{ Carbon\Carbon::parse($inscrito->data_inscricao)->format('d-m-Y') }}</td>
                    <td>{{ Str::limit($inscrito->empresa->nome_razao, 40) }}</td>
                    <td>{{ $inscrito->pessoa->nome_razao }}</td>
                    <td>
                        @if ($inscrito->confirmou)
                            <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i>
                        @else
                            <i class="ri-alarm-fill label-icon text-warning fs-xl ms-2"></i>
                        @endif
                    </td>
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
                                    <form method="POST"
                                        action="{{ route('materiais-padroes-delete', $inscrito['uid']) }}">
                                        @csrf
                                        <button class="dropdown-item" type="submit">Excluir</button>
                                    </form>
                                    <x-painel.form-delete.delete route='materiais-padroes-delete'
                                        id="{{ $inscrito->uid }}" />
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <x-painel.agendamento-cursos.modal-participante />
            @empty
                <tr>
                    <td colspan="6" class="text-center">Este Instrutor não possui cursos realizados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
