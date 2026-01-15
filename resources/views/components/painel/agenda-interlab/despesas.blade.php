@if( $errors->despesas->any() )
  <x-alerts.warning :errors="$errors->despesas->all()"/>
@endif

<div class="row">
  <div class="col-12 col-md-7 col-xxl-8 pe-3">
    <div class="row">
      <div class="col-12 d-flex justify-content-between my-3">
        <h6>Despesas do interlab</h6>
        <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#despesaModal">
          <i class="ri-add-line align-bottom me-1"></i> Adicionar despesa
        </a>
      </div>
      {{-- Tabela --}}
      <div class="table-responsive" style="min-height: 25vh">
        <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
          <thead>
            <tr>
              <th scope="col">Descricao</th>
              <th scope="col" style="width: 5%; white-space: nowrap;">Qtd</th>
              <th scope="col" style="width: 5%; white-space: nowrap;">Val unit</th>
              <th scope="col" style="width: 5%; white-space: nowrap;">Val total</th>
              <th scope="col" style="width: 5%; white-space: nowrap;"></th>
            </tr>
          </thead>
          <tbody>
              @forelse ( $interlabDespesa as $despesa)
              <tr>
                
                <td>{{ $despesa->materialPadrao->descricao }}</td>
                <td>{{ number_format($despesa->quantidade, 2, ',', '.') }}</td>
                <td>{{ "R$ " . number_format($despesa->valor, 2, ',', '.') }}</td>
                <td>{{ "R$ " . number_format($despesa->total, 2, ',', '.') }}</td>
                <td>
                  <div class="dropdown">
                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                      aria-expanded="false">
                      <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                      <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="{{ '#despesaModal' . $despesa->id }}">Editar</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('agenda-interlab-duplicar-despesa', $despesa->uid) }}" >Duplicar</a>
                      </li>
                      <li>
                        <x-painel.form-delete.delete route='delete-despesa' id="{{ $despesa->uid }}" />
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
              <x-painel.agenda-interlab.modal-despesa 
                :despesa="$despesa" 
                :agendainterlab="$agendainterlab" 
                :materiaisPadrao="$materiaisPadrao"
                :fornecedores="$fornecedores"
                :fabricantes="$fabricantes" />
              @empty
                <tr>
                  <td colspan="7" class="text-center">Não há despesas cadastradas</td>
                </tr>
              @endforelse
            
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-end fw-bold">Total:</td>
              <td class="fw-bold">
                {{ "R$ " . number_format($interlabDespesa->sum('total'), 2, ',', '.') }}
              </td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      
        <x-painel.agenda-interlab.modal-despesa 
          :agendainterlab="$agendainterlab" 
          :materiaisPadrao="$materiaisPadrao"
          :fornecedores="$fornecedores"
          :fabricantes="$fabricantes" />
      
      </div>
    </div>
  </div>
  <div class="col-12 col-md-5 col-xxl-4 ps-3">
    <div class="row">
      <h6>Parametros do interlab</h6>
      <form action="{{ route('salva-parametro') }}" method="post">
        @csrf
        <div class="col-12 d-flex justify-content-between gap-2 my-3">

          <input type="hidden" name="agenda_interlab_id" value="{{ $agendainterlab->id }}">

          <select name="parametro_id" class="form-select form-select-sm">
            <option value="">Selecione parametro para adicionar</option>
            @foreach ($parametros as $parametro)
            <option value="{{ $parametro->id }}">{{ $parametro->descricao }}</option>
            @endforeach
          </select>
  
          <button type="submit" class="btn btn-sm btn-success text-nowrap" >
            <i class="ri-add-line align-bottom me-1"></i> Adicionar
          </button>

          
        </div>
      </form>
      {{-- Tabela --}}
      <div class="table-responsive" style="min-height: 25vh">
        <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
          <thead>
            <tr>
              <th scope="col">Descricao</th>
              <th scope="col" style="width: 5%; white-space: nowrap;"></th>
            </tr>
          </thead>
          <tbody>
              @forelse ( $interlabParametros as $parametro)
              <tr>
                <td>{{ $parametro->parametro->descricao }}</td>
                <td>
                  <div class="dropdown">
                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                      aria-expanded="false">
                      <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                      <li>
                        <form class="form-delete " method="POST" id="delete-parametro-{{ $parametro->id }}" action="{{ route('delete-parametro', $parametro->id) }}">
                          @csrf
                          <input type="hidden" name="agenda_interlab_id" value="{{ $agendainterlab->id }}">
                          <input type="hidden" name="parametro_id" value="{{ $parametro->parametro->id }}">
                          <button class="dropdown-item botao-delete" type="button">Deletar</button>
                        </form>
                    </ul>
                  </div>
                </td>
              </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center">Não há parametros cadastrados</td>
                </tr>
              @endforelse
            
          </tbody>
        </table>
      
      </div>

    </div>
  </div>

</div>