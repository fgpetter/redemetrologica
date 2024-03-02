<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="{{ route('agendamento-curso-insert') }}" class="btn btn-sm btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Agendamento de curso
                </a>
            </div>
        </div>

        @if (session('agendamento-success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('agendamento-success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class=" d-sm-table-cell" style="width: 1%; white-space: nowrap;">Mês</th>
                        <th scope="col">COD</th>
                        <th scope="col">Status</th>
                        <th scope="col">Data Inicio</th>
                        <th scope="col">Nome do Curso</th>
                        <th scope="col" class="text-center">Carga Horaria</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agendacursos as $agendacurso)
                        <tr>
                            <th class="text-uppercase">
                                {{ Carbon\Carbon::parse($agendacurso->data_inicio)->locale('pt-BR')->translatedFormat('F') }}
                            </th>
                            <td class="text-center">{{ $agendacurso->id }}</td>
                            <td
                                @if ($agendacurso->status == 'CONFIRMADO') class="text-success fw-bold"
              @elseif ($agendacurso->status == 'REAGENDAR')
              class="text-primary fw-bold"
              @elseif ($agendacurso->status == 'CANCELADO')
              class="text-danger fw-bold" @endif>
                                {{ $agendacurso->status }}</td>
                            <td style="white-space: nowrap; margin: 10px 0 10px">
                                {{ Carbon\Carbon::parse($agendacurso->data_inicio)->format('d-m-Y') }}</td>
                            <td>{{ $agendacurso->curso->descricao ?? '' }}</td>
                            <td class="text-center">{{ $agendacurso->carga_horaria }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li><a class="dropdown-item"
                                                href="{{ route('agendamento-curso-insert', $agendacurso->uid) }}">Editar</a>
                                        </li>
                                        <li>
                                            <form method="POST"
                                                action="{{ route('agendamento-curso-delete', $agendacurso->uid) }}">
                                                @csrf
                                                <button class="dropdown-item" type="submit">Excluir</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Não há Agendamento de Cursos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
