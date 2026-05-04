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
                    <div class="row g-2">
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
                                    <option @selected($busca_pessoa == $pessoa->id) value="{{ $pessoa->id }}">{{ $pessoa->cpf_cnpj }} - {{ $pessoa->nome_razao }}</option>
                                @endforeach
                            </x-forms.input-select>
                        </div>
                        <div class="col-12 col-lg-2 d-flex gap-2 align-items-center justify-content-end">
                          <button type="submit" class="btn btn-primary ">Pesquisar</button>
                          <a href="{{ route('lancamento-financeiro-insert') }}" class="btn btn-success ">
                            <i class="ri-add-line align-bottom me-1"></i> Adicionar
                          </a>
                        </div>
                    </div>
                </form>
                <div class="col-12">
                  <form method="GET" action="{{ route('export-lancamentos') }}">
                    <div class="col-4">
                      <label for="mesano" class="form-label mt-2">Exportar Lançamentos do Mês</label>
                      <div class="input-group">
                        <select class="form-select" id="mesAnoExport" name="mesano">
                          <option value="">Selecione...</option>
                          @foreach($mesesanos as $mesano)
                            <option value="{{ $mesano }}">{{ $mesano }}</option>
                          @endforeach
                        </select>
      
                        <button type="submit" class="btn btn-outline-success disabled" id="linkExportLancamentos">
                          <i class="ri-download-2-line"></i> Baixar
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">

  {{-- RECEITAS --}}
  <div class="col-6">
    <div class="card border-start border-success border-4">
      <div class="card-header bg-success-subtle d-flex align-items-center justify-content-between">
        <h5>RECEITAS</h5>
        <button type="button"
          class="btn btn-primary btn-sm"
          id="btnEditarLoteReceitas"
          style="margin-top: -18px; display: none;"
          data-bs-toggle="modal"
          data-bs-target="#modalLoteReceitas">
          Editar em lote (<span id="contadorSelecionadosReceitas">0</span>)
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive" style="min-height: 25vh">
          <table class="table border-1" style="table-layout: fixed">
            <thead>
              <tr>
                <th scope="col" style="width: 2.5rem;"></th>
                <th scope="col">Nome</th>
                <th scope="col" style="width: 20%;">Vencimento</th>
                <th scope="col" style="width: 15%;">Valor</th>
                <th scope="col" style="width: 15%;">NF</th>
                <th scope="col" style="width: 5%;"></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($lancamentosfinanceiros->where('tipo_lancamento', 'CREDITO')->whereNotNull('data_pagamento') as $lancamento)
                <tr>
                  <td class="align-middle">
                    <input class="form-check-input js-batch-checkbox" type="checkbox" value="{{ $lancamento->uid }}">
                  </td>
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
                  <td colspan="6" class="p-0">
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
                <td colspan="6" class="border-0">
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
<div class="modal fade" id="modalLoteReceitas" tabindex="-1" aria-labelledby="modalLoteReceitasLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('lancamento-financeiro-batch-update') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalLoteReceitasLabel">Editar lançamentos em lote</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body d-flex flex-column gap-2">
          <div id="batchSelectedUids"></div>
          <x-forms.input-field
            label="Conciliação"
            type="text"
            id="lote_consiliacao"
            name="consiliacao"
          />
          <x-forms.input-field
            label="Nota fiscal"
            type="text"
            id="lote_nota_fiscal"
            name="nota_fiscal"
          />
          <x-forms.input-field
            label="Data de pagamento"
            type="date"
            id="lote_data_pagamento"
            name="data_pagamento"
          />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
  document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('mesAnoExport');
    const link = document.getElementById('linkExportLancamentos');
    const formLote = document.querySelector('#modalLoteReceitas form');
    const botaoLote = document.querySelector('[data-bs-target="#modalLoteReceitas"]');
    const contadorSelecionados = document.getElementById('contadorSelecionadosReceitas');
    const checkboxesLote = document.querySelectorAll('.js-batch-checkbox');
    const selectedUidsContainer = document.getElementById('batchSelectedUids');

    select.addEventListener('change', function () {
      if (this.value) {
        link.classList.remove('disabled');
      } else {
        link.classList.add('disabled');
      }
    });

    const atualizarEstadoEdicaoLote = () => {
      const checked = document.querySelectorAll('.js-batch-checkbox:checked').length;
      if (botaoLote) {
        botaoLote.style.display = checked > 0 ? '' : 'none';
      }
      if (contadorSelecionados) {
        contadorSelecionados.textContent = checked.toString();
      }
    };

    checkboxesLote.forEach((checkbox) => {
      checkbox.addEventListener('change', atualizarEstadoEdicaoLote);
    });

    atualizarEstadoEdicaoLote();

    if (formLote) {
      formLote.addEventListener('submit', function () {
        if (selectedUidsContainer) {
          selectedUidsContainer.innerHTML = '';
          document.querySelectorAll('.js-batch-checkbox:checked').forEach((checkbox) => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'uids[]';
            hiddenInput.value = checkbox.value;
            selectedUidsContainer.appendChild(hiddenInput);
          });
        }

      });
    }
  });
</script>