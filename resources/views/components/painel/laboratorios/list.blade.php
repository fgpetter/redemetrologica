<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12">
        <div class="hstack gap-2 flex-wrap mb-3 justify-content-end">
          <button class="btn btn-sm btn-success" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar Laboratório
          </button>
        </div>

        <div class="collapse" id="collapseExample">
          <div class="card mb-3 shadow-none">
              <div class="card-body">
                <form action="{{route('laboratorio-create')}}" method="POST">
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
          <th scope="col" style="width: 5%">ID</th>
          <th scope="col" style="width: 50%">Empresa</th>
          <th scope="col" style="width: 40%">Nome do Laboratório</th>
          <th scope="col" style="width: 5%"></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($laboratorios->where('pessoa' , '!=', null) as $laboratorio)
          <tr>
            <th scope="row">
              <a href="{{ route('laboratorio-insert', ['laboratorio' => $laboratorio->uid]) }}" class="fw-medium">
                  #{{ substr($laboratorio->uid, 7) }} 
                </a>
              </th>
            <td>{{$laboratorio->pessoa->nome_razao}}</td>
            <td>{{($laboratorio->nome_laboratorio != $laboratorio->pessoa->nome_razao) ? $laboratorio->nome_laboratorio : ''}}</td>
            <td>
              <div class="dropdown">
                <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem" 
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                  <li><a class="dropdown-item" href="{{route('laboratorio-insert', ['laboratorio' => $laboratorio->uid])}}">Editar</a></li>
                  <li>
                  
                    <x-painel.form-delete.delete route='laboratorio-delete' id="{{ $laboratorio->uid }}" />
                  </li>
                </ul>
              </div>

            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center" > Não há laboratorios na base. </td>
          </tr>
        @endforelse
      </tbody>
      </table>
      <div class="row mt-3">
        {!! $laboratorios->withQueryString()->links('pagination::bootstrap-5') !!}
      </div>
    </div>

  </div>
</div>