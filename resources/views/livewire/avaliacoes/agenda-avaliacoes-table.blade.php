<div>
    <div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12">
        <div class="hstack gap-2 flex-wrap mb-3 justify-content-end">
          <button class="btn btn-sm btn-success" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" 
            aria-expanded="false" aria-controls="collapseExample" >
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
                        @foreach($this->laboratorios as $laboratorio)
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

    {{-- Filtros --}}
      <div class="card border shadow-sm mb-3 mx-3" >
          <div class="card-body p-2">
              <div class="mb-2">
                  <h6 class="card-title mb-0">Filtros</h6>
              </div>

              <div class="row gx-3">
                  <!-- Data Inicial -->
                  <div class="col-12 col-sm-2">
                      <label class="form-label mb-0">Data Inicial</label>
                      <input wire:model.live="dataIni" class="form-control form-control-sm" type="date"
                          name="data_inicial" id="data_inicial">
                  </div>

                  <!-- Data Final -->
                  <div class="col-12 col-sm-2">
                      <label class="form-label mb-0">Data Final</label>
                      <input wire:model.live="dataFim" class="form-control form-control-sm" type="date"
                          name="data_final" id="data_final">
                  </div>

                  <div class="col-8 text-end d-none d-sm-block ">
                    <button wire:click="resetFilters" type="button" class="btn btn-sm btn-light text-danger mt-3 me-xxl-3">
                        <i class="ri-close-line"></i> Limpar
                    </button>
                  </div>

                  <!-- Comitê -->
                  <div class="col-12 col-sm-3 col-xxl-2 mt-3">
                      <label class="form-label mb-0">Comitê</label>
                      <select wire:model.live="comite" class="form-select form-select-sm">
                          <option value="">Selecione...</option>
                          <option value="APROVADO">APROVADO</option>
                          <option value="NÃO APROVADO">NÃO APROVADO</option>
                          <option value="COM PENDENCIAS">COM PENDENCIAS</option>
                      </select>
                  </div>

                  <!-- Status da proposta -->
                  <div class="col-12 col-sm-3 col-xxl-2 mt-3">
                      <label class="form-label mb-0">Status da proposta</label>
                      <select wire:model.live="status_proposta" class="form-select form-select-sm">
                          <option value="">Selecione...</option>
                          <option value="PENDENTE">PENDENTE</option>
                          <option value="AGUARDANDO">AGUARDANDO APROVACAO</option>
                          <option value="APROVADA">APROVADO</option>
                          <option value="REPROVADA">REPROVADO</option>
                      </select>
                  </div>

                  <!-- Tipo Avaliação -->
                  <div class="col-12 col-sm-3 col-xxl-2 mt-3">
                      <label class="form-label mb-0">Tipo Avaliação</label>
                      <select wire:model.live="tipo_avaliacao_id" class="form-select form-select-sm">
                          <option value="">Selecione...</option>
                          @foreach($this->tipos as $tipo)
                              <option value="{{ $tipo->id }}">{{ $tipo->descricao }}</option>
                          @endforeach
                      </select>
                  </div>

                  <!-- Avaliador -->
                  <div class="col-12 col-sm-3 col-xxl-2 mt-3">
                      <label class="form-label mb-0">Avaliador</label>
                      <select wire:model.live="avaliador_id" class="form-select form-select-sm">
                          <option value="">Selecione...</option>
                          @foreach($this->avaliadores as $avaliador)
                              <option value="{{ $avaliador->id }}">{{ $avaliador->pessoa->nome_razao }}</option>
                          @endforeach
                      </select>
                  </div>

                  {{-- Replica para responsivo Botão Limpar Filtros --}}
                  <div class="col-12 text-end d-block d-sm-none">
                    <button wire:click="resetFilters" type="button" class="btn btn-sm btn-light text-danger mt-3">
                        <i class="ri-close-line"></i> Limpar
                    </button>
                  </div>

              </div>
          </div>
      </div>
      {{-- Filtros --}}

    <div class="table-responsive" style="min-height: 25vh">
      <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
      <thead>
        <tr>
          <th scope="col" style="width: 10%;">
            <a href="#" wire:click.prevent="setSortBy('data_inicio')">
                Data Inicio
                @if ($sortBy == 'data_inicio')
                    @if ($sortDirection == 'ASC')
                        <i class="ri-arrow-up-s-line"></i>
                    @else
                        <i class="ri-arrow-down-s-line"></i>
                    @endif
                @endif
            </a>
          </th>
          <th scope="col" style="width: 25%;">
            <a href="#" wire:click.prevent="setSortBy('laboratorio_id')">
              Laboratório
              @if ($sortBy == 'laboratorio_id')
                  @if ($sortDirection == 'ASC')
                      <i class="ri-arrow-up-s-line"></i>
                  @else
                      <i class="ri-arrow-down-s-line"></i>
                  @endif
              @endif
            </a>
          </th>
          <th scope="col" style="width: 5%;">
            <a href="#" wire:click.prevent="setSortBy('fr_28')">
              FR28
              @if ($sortBy == 'fr_28')
                  @if ($sortDirection == 'ASC')
                      <i class="ri-arrow-up-s-line"></i>
                  @else
                      <i class="ri-arrow-down-s-line"></i>
                  @endif
              @endif
            </a>
          </th>
          <th scope="col" style="width: 10%;">
            <a href="#" wire:click.prevent="setSortBy('status_proposta')">
              Proposta
              @if ($sortBy == 'status_proposta')
                  @if ($sortDirection == 'ASC')
                      <i class="ri-arrow-up-s-line"></i>
                  @else
                      <i class="ri-arrow-down-s-line"></i>
                  @endif
              @endif
            </a>
          </th>
          <th scope="col" style="width: 10%;">
            <a href="#" wire:click.prevent="setSortBy('carta_marcacao')">
              Carta
              @if ($sortBy == 'carta_marcacao')
                  @if ($sortDirection == 'ASC')
                      <i class="ri-arrow-up-s-line"></i>
                  @else
                      <i class="ri-arrow-down-s-line"></i>
                  @endif
              @endif
            </a>
          </th>
          <th scope="col" style="width: 10%;">
            <a href="#" wire:click.prevent="setSortBy('data_proposta_acoes_corretivas')">
              Data Proposta
              @if ($sortBy == 'data_proposta_acoes_corretivas')
                  @if ($sortDirection == 'ASC')
                      <i class="ri-arrow-up-s-line"></i>
                  @else
                      <i class="ri-arrow-down-s-line"></i>
                  @endif
              @endif
            </a>
          </th>
          <th scope="col" style="width: 10%;">
            <a href="#" wire:click.prevent="setSortBy('data_acoes_corretivas')">
              Data Ações
              @if ($sortBy == 'data_acoes_corretivas')
                  @if ($sortDirection == 'ASC')
                      <i class="ri-arrow-up-s-line"></i>
                  @else
                      <i class="ri-arrow-down-s-line"></i>
                  @endif
              @endif
            </a>
          </th>
          <th scope="col" style="width: 10%;">
            <a href="#" wire:click.prevent="setSortBy('acoes_aceitas')">
              Ações Aceitas
              @if ($sortBy == 'acoes_aceitas')
                  @if ($sortDirection == 'ASC')
                      <i class="ri-arrow-up-s-line"></i>
                  @else
                      <i class="ri-arrow-down-s-line"></i>
                  @endif
              @endif
            </a>
          </th>
          <th scope="col" style="width: 10%;">
            <a href="#" wire:click.prevent="setSortBy('comite')">
              Comitê
              @if ($sortBy == 'comite')
                  @if ($sortDirection == 'ASC')
                      <i class="ri-arrow-up-s-line"></i>
                  @else
                      <i class="ri-arrow-down-s-line"></i>
                  @endif
              @endif
            </a>
          </th>
          <th scope="col" style="width: 5%;"></th>
        </tr>
      </thead>
      
      <tbody>
        @forelse ($avaliacoes as $avaliacao)
          <tr>
            <td>{{ $avaliacao->data_inicio ? \Carbon\Carbon::parse($avaliacao->data_inicio)->format('d/m/Y') : '' }}</td>
            <td style="max-width: 25ch; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
              title="{{ $avaliacao->laboratorio->nome_laboratorio }}">
                {{ $avaliacao->laboratorio->nome_laboratorio ?? $avaliacao->laboratorio->pessoa->nome_razao }}
            </td>
            <td> 
              @if ($avaliacao->fr_28) 
              <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i> @endif 
            </td>
            <td style="max-width: 15ch; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
              title="{{ $avaliacao->status_proposta }}">
                {{ $avaliacao->status_proposta }}
            </td>
            <td style="max-width: 15ch; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
              title="{{ $avaliacao->carta_marcacao }}">
                {{ $avaliacao->carta_marcacao }}
            </td>
            <td>
              {{ $avaliacao->data_proposta_acoes_corretivas ? \Carbon\Carbon::parse($avaliacao->data_proposta_acoes_corretivas)->format('d/m/Y') : '' }}
            </td>
            <td>
              {{ $avaliacao->data_acoes_corretivas ? \Carbon\Carbon::parse($avaliacao->data_acoes_corretivas)->format('d/m/Y') : '' }}
            </td>
            <td style="max-width: 15ch; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
              title="{{ $avaliacao->acoes_aceitas }}">
                {{ $avaliacao->acoes_aceitas }}
            </td>
            <td style="max-width: 15ch; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
              title="{{ $avaliacao->comite }}">{{ $avaliacao->comite }}
            </td>
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
            <div class="row mt-3 w-100">
                {{ $avaliacoes->links() }}
            </div>
    </div>

  </div>
</div>
</div>
