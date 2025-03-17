@php
    $order = request('order', 'asc') == 'asc' ? 'desc' : 'asc';
    $currentOrderBy = request('orderBy', 'data_inicio');
    $busca_nome = request('buscanome', '');
@endphp
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12 d-flex justify-content-end mb-3">
        <a href="{{ ($tipoagenda == 'IN-COMPANY') ? route('agendamento-curso-in-company-insert') : route('agendamento-curso-insert') }}" 
          class="btn btn-sm btn-success">
          <i class="ri-add-line align-bottom me-1"></i> Adicionar Agendamento de curso
        </a>
      </div>
    </div>


    <div class="table-responsive" style="min-height: 25vh">
      <div>
        <tr>
          <th colspan="4"></th>
          <th colspan="2">
            <input type="text" class="form-control form-control-sm" 
              onkeypress="search(event, window.location.href, 'buscanome')"
              placeholder="Buscar por curso" value="{{ $busca_nome }}">
          </th>
          <th colspan="3"></th>
        </tr>
      </div>
      <table class="table table-responsive table-striped align-middle mb-0">
        <!-- Cabeçalho ordenável -->
        <thead>
          <tr>
            <th scope="col" style="width: 5%; white-space: nowrap;">Mês</th>
            <th scope="col" style="width: 5%; white-space: nowrap;">ID</th>
            <th scope="col" style="width: 7%; white-space: nowrap;">
              <a href="{{ request()->fullUrlWithQuery(['orderBy' => 'status', 'order' => $order]) }}">
                Status {!! $currentOrderBy == 'status' ? ($order == 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>') : '' !!}
              </a>
            </th>
            <th scope="col"></th>
            <th scope="col" style="width: 7%; white-space: nowrap;">
              <a href="{{ request()->fullUrlWithQuery(['orderBy' => 'data_inicio', 'order' => $order]) }}">
                Data Inicio {!! $currentOrderBy == 'data_inicio' ? ($order == 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>') : '' !!}
              </a>
            </th>
            <th scope="col">
              <a href="{{ request()->fullUrlWithQuery(['orderBy' => 'curso', 'order' => $order]) }}">
                Nome do Curso {!! $currentOrderBy == 'curso' ? ($order == 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>') : '' !!}
              </a>
            </th>
            <th scope="col">Tipo</th>
            <th scope="col" class="text-center">Inscritos</th>
            <th scope="col" style="width: 5%; white-space: nowrap;"></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($agendacursos as $agendacurso)
            <tr>
              <th class="text-uppercase">
                {{ Carbon\Carbon::parse($agendacurso->data_inicio)->locale('pt-BR')->translatedFormat('F') }}
              </th>
              <td class="text-center text-nowrap">
                <a href="{{ ($tipoagenda == 'IN-COMPANY') 
                  ? route('agendamento-curso-in-company-insert', $agendacurso->uid) 
                  : route('agendamento-curso-insert', $agendacurso->uid) }}">
                  # {{ $agendacurso->id }}</a> 
                </td>
              <td
                @if ($agendacurso->status == 'CONFIRMADO') class="text-success fw-bold"
                @elseif ($agendacurso->status == 'REAGENDAR') class="text-primary fw-bold"
                @elseif ($agendacurso->status == 'CANCELADO') class="text-danger fw-bold" @endif>
                {{ $agendacurso->status }}
              </td>

              <td class="text-center text-nowrap">
                @if ($agendacurso->site)
                  <span data-bs-toggle="tooltip" data-bs-html="true" title="Visivel no site">
                    <i class="ri-terminal-window-line label-icon text-primary" style="font-size: 1.4rem"></i> 
                  </span>
                  
                @endif
                @if ($agendacurso->inscricoes) &nbsp;
                  <span data-bs-toggle="tooltip" data-bs-html="true" title="Inscrições abertas">
                    <i class="ri-edit-2-fill label-icon text-success" style="font-size: 1.4rem"></i> 
                  </span>
                 @endif
              </td>

              <td class="text-center text-nowrap mx-2">
                {{ \Carbon\Carbon::parse($agendacurso->data_inicio)->format('d/m/Y') }}</td>
              <td>{{ $agendacurso->curso->descricao ?? '' }}</td>
              <td>{{ $agendacurso->tipo_agendamento ?? '' }}</td>
              <td class="text-center">
                {{ ($agendacurso->tipo_agendamento != 'IN-COMPANY') 
                    ? $agendacurso->inscritos->where('valor', '!=', null)->count()
                    : $agendacurso->inscritos->count() }}
              </td>
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
                        href="{{ ($tipoagenda == 'IN-COMPANY') 
                            ? route('agendamento-curso-in-company-insert', $agendacurso->uid) 
                            : route('agendamento-curso-insert', $agendacurso->uid) }}">
                        Editar</a>
                    </li>
                    <li>

                      <x-painel.form-delete.delete route='agendamento-curso-delete'
                        id="{{ $agendacurso->uid }}" />
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
       <div class="row mt-3 w-100">
        {!! $agendacursos->appends(request()->query())->links('pagination::bootstrap-5') !!}
      </div>
    </div>
  </div>
</div>
