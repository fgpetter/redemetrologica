<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12 d-flex justify-content-end mb-3">
        <a href="{{ route('agenda-interlab-insert') }}" class="btn btn-sm btn-success">
          <i class="ri-add-line align-bottom me-1"></i> Adicionar agenda interlab
        </a>
      </div>
    </div>

    <div class="table-responsive" style="min-height: 25vh">
      <table class="table table-responsive table-striped align-middle mb-0">
        <thead>
          <tr>
            <th scope="col" style="width: 5%; white-space: nowrap;">Mês/Ano</th>
            <th scope="col" style="width: 5%; white-space: nowrap;">COD</th>
            <th scope="col" style="width: 7%; white-space: nowrap;">Status</th>
            <th scope="col" ></th>
            <th scope="col" style="width: 7%; white-space: nowrap;">Data Inicio</th>
            <th scope="col" style="width: 7%; white-space: nowrap;">Data Fim</th>
            <th scope="col" >Nome do Interlab</th>
            <th scope="col" style="width: 5%; white-space: nowrap;">Inscritos</th>
            <th scope="col" style="width: 5%; white-space: nowrap;"></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($agendainterlabs as $agendainterlab)
            <tr class="clicable" href="{{ route('agenda-interlab-insert', $agendainterlab->uid) }}">

              <th class="text-uppercase">
                {{ Carbon\Carbon::parse($agendainterlab->data_inicio)->format('m/Y') }}
              </th>

              <td class="text-center text-nowrap">
                <a href="{{ route('agenda-interlab-insert', $agendainterlab->uid) }}">#{{ $agendainterlab->id }}</a>
              </td>

              <td
                @if ($agendainterlab->status == 'CONFIRMADO') class="text-success fw-bold"
                @elseif ($agendainterlab->status == 'AGENDADO') class="text-primary fw-bold"
                @elseif ($agendainterlab->status == 'CONCLUIDO') class="text-danger fw-bold" @endif>
                {{ $agendainterlab->status }}
              </td>

              <td style="white-space: nowrap;">
                @if ($agendainterlab->site)
                  <span data-bs-toggle="tooltip" data-bs-html="true" title="Visivel no site">
                    <i class="ri-terminal-window-line label-icon text-primary" style="font-size: 1.4rem"></i> 
                  </span>
                  
                @endif
                @if ($agendainterlab->inscricao) &nbsp;
                  <span data-bs-toggle="tooltip" data-bs-html="true" title="Inscrições abertas">
                    <i class="ri-edit-2-fill label-icon text-success" style="font-size: 1.4rem"></i> 
                  </span>
                 @endif
              </td>

              <td> {{ $agendainterlab->data_inicio?->format('d/m/Y') }} </td>

              <td> {{ $agendainterlab->data_fim?->format('d/m/Y') }} </td>

              <td> {{ $agendainterlab->interlab->nome ?? '' }} </td>
              <td class="text-end pe-2"> {{ $agendainterlab->inscritos->count() }} </td>

              <td>
                <div class="dropdown">
                  <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ph-dots-three-outline-vertical" 
                      style="font-size: 1.5rem"
                      data-bs-toggle="tooltip" 
                      data-bs-placement="top" 
                      title="Detalhes e edição">
                    </i>
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                    <li><a class="dropdown-item" href="{{ route('agenda-interlab-insert', $agendainterlab->uid) }}">Editar</a></li>
                    <li><x-painel.form-delete.delete route='agenda-interlab-delete' id="{{ $agendainterlab->uid }}" /></li>
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
