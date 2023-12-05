  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-12 d-flex justify-content-end mb-3">
          <a href="{{ route('avaliador-insert') }}" class="btn btn-sm btn-success" > 
            <i class="ri-add-line align-bottom me-1"></i> Adicionar 
          </a>
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
            <th scope="col" class="d-none d-sm-table-cell" style="width: 1%; white-space: nowrap;">ID</th>
            <th scope="col">Nome</th>
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
              <th scope="row" class="d-none d-sm-table-cell">
                <a href="{{ route('avaliador-insert', ['avaliador' => $avaliador->uid]) }}" class="fw-medium">
                   #{{ substr($avaliador->uid, 7) }} 
                  </a>
                </th>
              <td>{{$avaliador->pessoa->nome_razao}}</td>
              <td>@if ($avaliador->exp_min_comprovada) <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i> @endif</td>
              <td>@if ($avaliador->curso_incerteza) <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i> @endif</td>
              <td>@if ($avaliador->curso_iso) <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i> @endif</td>
              <td>@if ($avaliador->curso_aud_interna) <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i> @endif</td>
              <td>{{Carbon\Carbon::parse($avaliador->ingresso)->format('d/m/Y')}}</td>
              <td>
                <div class="dropdown">
                  <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem" 
                      data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    <li><a class="dropdown-item" href="{{route('avaliador-insert', ['avaliador' => $avaliador->uid])}}">Editar</a></li>
                    <li>
                      <form method="POST" action="{{ route('avaliador-delete', $avaliador->uid) }}">
                        @csrf
                        <button class="dropdown-item" type="submit">Deletar</button>
                      </form>
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
      </div>

    </div>
  </div>