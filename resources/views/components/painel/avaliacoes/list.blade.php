<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12">
        <div class="hstack gap-2 flex-wrap mb-3 justify-content-end">
          <button class="btn btn-sm btn-success" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar Avaliação
          </button>
        </div>

        <div class="collapse" id="collapseExample">
          <div class="card mb-3 shadow-none">
              <div class="card-body">
                <form action="{{route('avaliacao-create')}}" method="POST">
                  @csrf
                  <div class="row">
                    <div class="col-10">
                      <select class="form-control" data-choices name="laboratorio_uid" id="choices-single-default">
                        <option value="">Selecione na lista</option>
                        @foreach($laboratorios as $laboratorio)
                          <option value="{{ $laboratorio->uid }}">{{ ($laboratorio->nome_laboratorio) ? $laboratorio->nome_laboratorio : $laboratorio->pessoa->nome_razao }}</option>
                        @endforeach
                      </select>
                      @error('laboratorio_uid')<div class="text-warning">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-2">
                      <button class="btn btn-success" type="submit">Adicionar</button>
                    </div>
                  </div>
                </form>
                <p>Caso o laboratorio não esteja cadastrada ainda, <a href="{{ route('laboratorio-index') }}">Clique Aqui</a></p>
                
              </div>
          </div>
        </div>
      </div>
    </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="table-responsive" style="min-height: 25vh">
      <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
      <thead>
        <tr>
          <th scope="col" style="width: 5%">Data Inicio</th>
          <th scope="col" >Laboratório</th>
          <th scope="col" style="width: 5%"> FR28 </th>
          <th scope="col" style="width: 5%; white-space: nowrap"> Proposta </th>
          <th scope="col" style="width: 5%; white-space: nowrap"> Carta </th>
          <th scope="col" style="width: 5%">Data Proposta</th>
          <th scope="col" style="width: 5%">Data Ações</th>
          <th scope="col" style="width: 5%">Ações Aceitas</th>
          <th scope="col" style="width: 5%; white-space: nowrap">Comitê</th>
          <th scope="col" style="width: 5%;"></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($avaliacoes as $avaliacao)
          <tr>
            <td>{{ $avaliacao->data_inicio }}</td>
            <td>{{ $avaliacao->laboratorio->nome_laboratorio }}</td>
            <td> @if ($avaliacao->fr_28) <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i> @endif </td>
            <td>{{ $avaliacao->status_proposta }}</td>
            <td>{{ $avaliacao->carta_marcacao }}</td>
            <td>{{ $avaliacao->data_proposta_acoes_corretivas }}</td>
            <td>{{ $avaliacao->data_acoes_corretivas }}</td>
            <td>{{ $avaliacao->acoes_aceitas }}</td>
            <td>{{ $avaliacao->comite }}</td>
            <td>
              <div class="dropdown">
                <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem" 
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                  <li><a class="dropdown-item" href="{{ route('avaliacao-insert', ['avaliacao' => $avaliacao->uid]) }}">Editar</a></li>
                  <li>
                    <x-painel.form-delete.delete route='avaliacao-delete' id="{{ $avaliacao->uid }}" />
                  </li>
                </ul>
              </div>

            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="text-center" > Não há avaliações agendadas </td>
          </tr>
        @endforelse
      </tbody>
      </table>
      {{-- <div class="row mt-3 w-100">
        {!! $laboratorios->withQueryString()->links('pagination::bootstrap-5') !!}
      </div> --}}
    </div>

  </div>
</div>