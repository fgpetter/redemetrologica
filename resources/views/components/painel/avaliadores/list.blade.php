  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-12">
          <div class="hstack gap-2 flex-wrap mb-3 justify-content-end">
            <button class="btn btn-sm btn-success" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
              <i class="ri-add-line align-bottom me-1"></i> Adicionar Avaliador
            </button>
          </div>

          <div class="collapse" id="collapseExample">
            <div class="card mb-3 shadow-none">
                <div class="card-body">
                  <form action="{{route('avaliador-create')}}" method="POST">
                    @csrf
                    <div class="row">
                      <div class="col-10">                      
                        <select class="form-control" data-choices name="pessoa_uid" id="choices-single-default">
                          <option value="">Selecione na lista</option>
                          @foreach($pessoas as $pessoa)
                            <option value="{{ $pessoa->uid }}">{{ $pessoa->cpf_cnpj }} | {{ $pessoa->nome_razao }}</option>
                          @endforeach
                        </select>
                        @error('pessoa_uid')<div class="text-warning">{{ $message }}</div>@enderror
                      </div>
                      <div class="col-2">
                        <button class="btn btn-success" type="submit">Adicionar</button>
                      </div>
                    </div>
                  </form>
                  <p>Caso a pessoa não esteja cadastrada ainda, <a href="{{ route('pessoa-insert') }}">Clique Aqui</a></p>
                  
                </div>
            </div>
          </div>
        </div>
      </div>

      @if (session('avaliador-success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('avaliador-success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <div class="table-responsive" style="min-height: 25vh">
        <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
          <tr>
            <th scope="col" style="width: 5%">ID</th>
            <th scope="col" style="width: 50%">Nome</th>
            <th scope="col">Experiencia</th>
            <th scope="col">Incerteza</th>
            <th scope="col">ISO</th>
            <th scope="col">Audit Interna</th>
            <th scope="col">Ingresso</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($avaliadores as $avaliador)
            <tr>
              <th scope="row">
                <a href="{{ route('avaliador-insert', ['avaliador' => $avaliador->uid]) }}" class="fw-medium">
                   #{{ substr($avaliador->uid, 7) }} 
                  </a>
                </th>
              <td>{{$avaliador->pessoa->nome_razao}}</td>
              <td>@if ($avaliador->exp_min_comprovada) <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i> @endif</td>
              <td>@if ($avaliador->curso_incerteza) <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i> @endif</td>
              <td>@if ($avaliador->curso_iso) <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i> @endif</td>
              <td>@if ($avaliador->curso_aud_interna) <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i> @endif</td>
              <td>{{ $avaliador->data_ingresso?->format('d/m/Y') }}</td>
              <td>
                <div class="dropdown">
                  <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem" 
                      data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    <li><a class="dropdown-item" href="{{route('avaliador-insert', ['avaliador' => $avaliador->uid])}}">Editar</a></li>
                    <li>
                   
                      <x-painel.form-delete.delete route='avaliador-delete' id="{{ $avaliador->uid }}" />
                    </li>
                  </ul>
                </div>
  
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center" > Não há avaliadors na base. </td>
            </tr>
          @endforelse
        </tbody>
        </table>
        <div class="row mt-3">
          {!! $avaliadores->withQueryString()->links('pagination::bootstrap-5') !!}
        </div>
      </div>

    </div>
  </div>