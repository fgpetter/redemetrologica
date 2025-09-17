 @php
    $order = request('name', 'asc') == 'asc' ? 'desc' : 'asc';
    $busca_nome = request('buscanome', '');
    $busca_area = request('buscaarea', '');
@endphp

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
        <table class="table table-responsive table-striped align-middle table-nowrap mb-0 table-hover">
       <!-- Cabeçalho de busca -->
        <thead>
          <tr>
            <th scope="col" style="padding: 0.5rem"></th>
            <th scope="col" style="padding: 0.5rem; min-width: 200px;">
              <select class="form-select form-select-sm" onchange="searchSelect(event, window.location.href, 'buscasituacao')">
                <option value="">Selecione</option>
                <option value="ATIVO" @if(request('buscasituacao') == 'ATIVO') selected @endif>ATIVO</option>
                <option value="AVALIADOR" @if(request('buscasituacao') == 'AVALIADOR') selected @endif>AVALIADOR</option>
                <option value="AVALIADOR EM TREINAMENTO" @if(request('buscasituacao') == 'AVALIADOR EM TREINAMENTO') selected @endif>AVALIADOR EM TREINAMENTO</option>
                <option value="AVALIADOR LIDER" @if(request('buscasituacao') == 'AVALIADOR LIDER') selected @endif>AVALIADOR LIDER</option>
                <option value="ESPECIALISTA" @if(request('buscasituacao') == 'ESPECIALISTA') selected @endif>ESPECIALISTA</option>
                <option value="INATIVO" @if(request('buscasituacao') == 'INATIVO') selected @endif>INATIVO</option>
              </select>
            </th>
            <th scope="col" style="padding: 0.5rem">
              <input type="text" class="form-control form-control-sm"
                onkeypress="search(event, window.location.href, 'buscanome')"
                placeholder="Buscar por nome" value="{{ $busca_nome ?? '' }}">
            </th>
            <th scope="col" style="padding: 0.5rem">
              <input type="text" class="form-control form-control-sm"
                onkeypress="search(event, window.location.href, 'buscaarea')"
                placeholder="Buscar por área" value="{{ $busca_area ?? '' }}">
            </th>
            <th scope="col" style="padding: 0.5rem"></th>
          </tr>
        </thead>
        <!-- Cabeçalho de busca -->
        <!-- Cabeçalho ordenável -->
        <thead>
          <tr>
            <th scope="col" style="width: 5%; padding: 0.75rem 0.5rem;">ID</th>
            <th scope="col" style="width: 5%; padding: 0.75rem 0.5rem;">Situação</th>
            <th scope="col" style="width: 20%; padding: 0.75rem 0.5rem;">
              <a href="{{ route('avaliador-index', [
                'name' => $order
                ]) }}" class="text-dark text-decoration-none">
                Nome 
                {!! $order == 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>' !!}
              </a>
            </th>
            <th scope="col" style="padding: 0.75rem 0.5rem;">Áreas</th>
            <th scope="col" style="width: 5%; padding: 0.75rem 0.5rem;"></th>
          </tr>
        </thead>
        <!-- Cabeçalho ordenável -->
        <tbody>
          @forelse ($avaliadores->where('pessoa' , '!=', null) as $avaliador)
            <tr>
              <th scope="row" style="padding: 0.75rem 0.5rem;">
                <a href="{{ route('avaliador-insert', ['avaliador' => $avaliador->uid]) }}" class="fw-medium">
                   #{{ substr($avaliador->uid, 7) }} 
                  </a>
                </th>
              <td style="padding: 0.75rem 0.5rem;">{{$avaliador->situacao ?? '--'}}</td>
              <td style="padding: 0.75rem 0.5rem;">{{$avaliador->pessoa->nome_razao}}</td>
              <td style="padding: 0.75rem 0.5rem;">   
                
                @php 
                $areasatuacao = $avaliador->areas
                    ->pluck('area.descricao')    
                    ->unique()                   
                    ->values();                  
                @endphp
            
                <div class="d-inline-block text-truncate" style="max-width: 300px; white-space: nowrap;">
                 @if($areasatuacao->isNotEmpty())
                   @foreach ($areasatuacao as $areaDescricao)
                     {{ $areaDescricao }}
                     @if (! $loop->last), @endif
                   @endforeach
                 @else
                   --
                 @endif
               </div>

              </td>
              
              <td style="padding: 0.75rem 0.5rem;">
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
              <td colspan="8" class="text-center" style="padding: 1.5rem;"> Não há avaliadors na base. </td>
            </tr>
          @endforelse
        </tbody>
        </table>
        <div class="row mt-3 w-100">
          {!! $avaliadores->withQueryString()->links('pagination::bootstrap-5') !!}
        </div>
      </div>

    </div>
  </div>