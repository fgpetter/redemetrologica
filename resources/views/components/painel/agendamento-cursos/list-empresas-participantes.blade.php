@props([
    'empresas' => [],
])


@if (session('update-success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('update-success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="table-responsive" style="min-height: 25vh">
    <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <th scope="col">Empresa</th>
                <th scope="col">Participantes</th>
                <th scope="col" style="width: 5%; white-space: nowrap;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($empresas as $empresa)
                <tr>
                    <td>{{ $empresa->pessoa->nome_razao }}</td>
                    <td>{{ $empresa->participantes }}</td>
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
                                        id="{{ $empresa->uid }}" lavel='' />
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
