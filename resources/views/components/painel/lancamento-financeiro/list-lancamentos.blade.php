@php
  if (isset($_GET['data_inicial']) && $_GET['data_inicial'] != "") {
    $data_inicial = \Carbon\Carbon::parse($_GET['data_inicial'])->format('Y-m-d');
  } else {
    $data_inicial = \Carbon\Carbon::now()->format('Y-m-d');
  }

  if (isset($_GET['data_final']) && $_GET['data_final'] != "") {
    $data_final = \Carbon\Carbon::parse($_GET['data_final'])->format('Y-m-d');
  } else {
    $data_final = \Carbon\Carbon::now()->addDays(7)->format('Y-m-d');
  }

  (isset($_GET['pessoa']) && $_GET['pessoa'] != "" ) ? $busca_pessoa = $_GET['pessoa'] : $busca_pessoa = null;

  isset($_GET['tipo_data']) && $_GET['tipo_data'] != '' ? ($tipo_data = $_GET['tipo_data']) : ($tipo_data = null);
@endphp
<div class="row my-3">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <form method="GET">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-lg-2">
                            <x-forms.input-field :value="$data_inicial ?? null" type="date" name="data_inicial" id="data_inicial"
                                label="Data Inicial" />
                        </div>
                        <div class="col-12 col-lg-2">
                            <x-forms.input-field :value="$data_final ?? null" type="date" name="data_final" id="data_final"
                                label="Data Final" />
                        </div>
                        <div class="col-12 col-lg-2">
                            <x-forms.input-select name="tipo_data" label="Filtrar por">
                                <option @selected($tipo_data == 'data_vencimento') value="data_vencimento">Vencimento</option>
                                <option @selected($tipo_data == 'data_pagamento') value="data_pagamento">Pagamento</option>
                            </x-forms.input-select>
                        </div>
                        <div class="col-12 col-lg-4">
                            <x-forms.input-select name="pessoa" id="pessoa" label="Pessoa">
                                <option value=""> - </option>
                                @foreach ($pessoas as $pessoa)
                                    <option @selected($busca_pessoa == $pessoa->id) value="{{ $pessoa->id }}">
                                        {{ $pessoa->cpf_cnpj }} - {{ $pessoa->nome_razao }}
                                    </option>
                                @endforeach
                            </x-forms.input-select>
                        </div>
                        <div class="col-12 col-lg-2 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Pesquisar</button>
                            <a href="{{ route('lancamento-financeiro-insert') }}" class="btn btn-sm btn-success w-100">
                                <i class="ri-add-line align-bottom me-1"></i> Adicionar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">

  {{-- RECEITAS --}}
  <div class="col-6">
    <div class="card border-start border-success border-4">
      <div class="card-header bg-success-subtle">
        <h5>RECEITAS</h5>
      </div>
      <div class="card-body">
            
    
        <div class="table-responsive" style="min-height: 25vh">
          <table class="table border-1" style="table-layout: fixed">
            <thead>
              <tr>
                <th scope="col" >Nome</th>
                <th scope="col" style="width: 20%;">Vencimento</th>
                <th scope="col" style="width: 15%;">Valor</th>
                <th scope="col" style="width: 15%;">NF</th>
                <th scope="col" style="width: 5%;"></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($lancamentosfinanceiros->where('tipo_lancamento', 'CREDITO')->whereNotNull('data_pagamento') as $lancamento)
                <tr>
                  <td class="text-truncate">
                    <a data-bs-toggle="collapse" href="{{"#collapse".$lancamento->uid}}" role="button" aria-expanded="false" aria-controls="collapseExample">
                      <i class="ri-file-text-line btn-ghost  pe-1 fs-5"></i>
                    </a> {{ $lancamento->pessoa->nome_razao }}
                  </td>
                  <td>{{ ($lancamento->data_vencimento) ? Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : '-'  }} </td>
                  
                  <td> <input type="text" class="money border-0 bg-transparent" value="{{ $lancamento->valor }}"> </td>
                  <td>  {{ $lancamento->nota_fiscal ?? '-' }} </td>
                  <td>
                    <div class="dropdown">
                      <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                          data-bs-toggle="tooltip" data-bs-placement="top"
                          title="Detalhes e edição"></i>
                      </a>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                        <li><a class="dropdown-item"
                            href="{{ route('lancamento-financeiro-insert', ['lancamento' => $lancamento->uid]) }}">Editar</a>
                        </li>
                        <li>
                          <x-painel.form-delete.delete route="lancamento-financeiro-delete"
                            id="{{ $lancamento->uid }}" />
                        </li>
                      </ul>
                    </div>
    
                  </td>
                </tr>
                <tr>
                  <td colspan="5" class="p-0">
                    <div class="collapse" id="{{"collapse".$lancamento->uid}}">
                      <div class="row gy-2 m-3 mt-2">
                        <div class="col-12"><b>Historico:</b> {{ $lancamento->historico ?? '-' }}</div>
                        <div class="col-3"><b>Vencimento:</b> {{ ($lancamento->data_vencimento) ? Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : '-'  }}</div>
                        <div class="col-3"><b>Documento:</b> {{ $lancamento->documento ?? '-' }}</div>
                        <div class="col-3"><b>Nº Documento:</b> {{ $lancamento->num_documento ?? '-' }}</div>
                        <div class="col-3"><b>Status:</b> {{ $lancamento->status ?? '-' }}</div>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center"> Não há lançamentos com vencimento nos próximos 7 dias, use o botão "Pesquisar" para atualizar a lista. </td>
                </tr>
              @endforelse
              <tr>
                <td colspan="5" class="border-0"> 
                  <h6> Total da seleção: R$ {{ $lancamentosfinanceiros->where('tipo_lancamento', 'CREDITO')->whereNotNull('data_pagamento')->sum('valor') }} </h6> 
                </td>
              </tr>
            </tbody>
          </table>
        </div>
    
      </div>
    </div>
  </div>

  {{-- DESPESAS --}}
  <div class="col-6">
    <div class="card border-start border-danger border-4">
      <div class="card-header bg-danger-subtle">
        <h5>DESPESAS</h5>
      </div>
      <div class="card-body">
    
        <div class="table-responsive" style="min-height: 25vh">
          <table class="table border-1" style="table-layout: fixed">
            <thead>
              <tr>
                <th scope="col" >Nome</th>
                <th scope="col" style="width: 20%;">Vencimento</th>
                <th scope="col" style="width: 15%;">Valor</th>
                <th scope="col" style="width: 20%;">Pagamento</th>
                <th scope="col" style="width: 5%;"></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($lancamentosfinanceiros->where('tipo_lancamento', 'DEBITO') as $lancamento)
                <tr>
                  <td class="text-truncate">
                    <a data-bs-toggle="collapse" href="{{"#collapse".$lancamento->uid}}" role="button" aria-expanded="false" aria-controls="collapseExample">
                      <i class="ri-file-text-line btn-ghost  pe-1 fs-5"></i>
                    </a> {{ $lancamento->pessoa->nome_razao }}
                  </td>
                  <td>{{ ($lancamento->data_vencimento) ? Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : '-'  }} </td>
                  
                  <td> <input type="text" class="money border-0 bg-transparent" value="{{ $lancamento->valor }}"> </td>
                  <td> {!! ($lancamento->data_pagamento) ? Carbon\Carbon::parse($lancamento->data_pagamento)->format('d/m/Y') : "<span class='badge rounded-pill bg-warning'>Em Aberto</span>" !!} </td>
                  <td>
                    <div class="dropdown">
                      <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                          data-bs-toggle="tooltip" data-bs-placement="top"
                          title="Detalhes e edição"></i>
                      </a>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                        <li><a class="dropdown-item"
                            href="{{ route('lancamento-financeiro-insert', ['lancamento' => $lancamento->uid]) }}">Editar</a>
                        </li>
                        <li>
                          <x-painel.form-delete.delete route="lancamento-financeiro-delete"
                            id="{{ $lancamento->uid }}" />
                        </li>
                      </ul>
                    </div>
    
                  </td>
                </tr>
                <tr>
                  <td colspan="5" class="p-0">
                    <div class="collapse" id="{{"collapse".$lancamento->uid}}">
                      <div class="row gy-2 m-3 mt-2">
                        <div class="col-12"><b>Historico:</b> {{ $lancamento->historico ?? '-' }}</div>
                        <div class="col-3"><b>Vencimento:</b> {{ ($lancamento->data_vencimento) ? Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : '-'  }}</div>
                        <div class="col-3"><b>Documento:</b> {{ $lancamento->documento ?? '-' }}</div>
                        <div class="col-3"><b>Nº Documento:</b> {{ $lancamento->num_documento ?? '-' }}</div>
                        <div class="col-6"><b>Nota Fiscal:</b> {{ $lancamento->nota_fiscal ?? '-' }}</div>
                        <div class="col-6"><b>Status:</b> {{ $lancamento->status ?? '-' }}</div>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center"> Não há lançamentos com vencimento nos próximos 7 dias, use o botão "Pesquisar" para atualizar a lista. </td>
                </tr>
              @endforelse
              <tr>
                <td colspan="5" class="border-0"> 
                  <h6> Total da seleção: R$ {{ $lancamentosfinanceiros->where('tipo_lancamento', 'DEBITO')->sum('valor') }} </h6> 
                </td>
              </tr>

            </tbody>
          </table>
        </div>
    
      </div>
    </div>
  </div>
</div>

