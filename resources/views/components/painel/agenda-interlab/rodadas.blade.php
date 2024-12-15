@if( $errors->rodadas->any() )
  <div class="alert alert-warning">
    <ul>
        @foreach ($errors->rodadas->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
  </div>
@endif

<div class="row">
  <div class="col-12 d-flex justify-content-end my-3">
    <a href="javascript:void(0)" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#rodadaModal">
      <i class="ri-add-line align-bottom me-1"></i> Adicionar
    </a>
  </div>
</div>

<div class="table-responsive" style="min-height: 25vh">
  <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
    <thead>
      <tr>
        <th scope="col">Descricao</th>
        <th scope="col">Cronograma</th>
        <th scope="col">Parametros dessa rodada</th>
        <th scope="col" style="width: 5%; white-space: nowrap;">Vias</th>
        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
      </tr>
    </thead>
    <tbody>
        @forelse ($rodadas as $rodada)
        <tr>
          
          <td>{{ $rodada->descricao }}</td>
          <td style="width: 30%; white-space: normal;">{!! nl2br($rodada->cronograma) !!}</td>
          <td style="width: 30%; white-space: normal;">
            <ul>
              @forelse ($rodada->parametros as $parametro)
              <li>{{ $parametro->parametro->descricao }}</li>
              @empty Nenhum Parametro 
              @endforelse
            </ul>
          </td>
          <td class="text-center">{{ $rodada->vias }}</td>
          <td>
            <div class="dropdown">
              <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                  data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
              </a>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                <li>
                  <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="{{ '#rodadaModal' . $rodada->uid }}">Editar</a>
                </li>
                <li>
                  <x-painel.form-delete.delete route='delete-rodada' id="{{ $rodada->uid }}" />
                </li>
              </ul>
            </div>
          </td>
        </tr>

        <x-painel.agenda-interlab.modal-rodada 
          :rodada="$rodada"
          :agendainterlab="$agendainterlab"
          :interlabParametros="$interlabParametros" />

        @empty
          <tr>
            <td colspan="7" class="text-center">Não há rodadas cadastradas</td>
          </tr>
        @endforelse
      
    </tbody>
  </table>

  <x-painel.agenda-interlab.modal-rodada 
    :agendainterlab="$agendainterlab"
    :interlabParametros="$interlabParametros" />

</div>
