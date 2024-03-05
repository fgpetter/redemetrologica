<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12 d-flex justify-content-end mb-3">
        <a href="{{ route('lancamento-financeiro-insert') }}" class="btn btn-sm btn-success">
          <i class="ri-add-line align-bottom me-1"></i> Adicionar
        </a>
      </div>
    </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="table-responsive" style="min-height: 25vh">
      <table class="table table-responsive table-striped align-middle table-nowrap mb-0"
        style="table-layout: fixed">
        <thead>
          <tr>
            <th scope="col">Nome</th>
            <th scope="col">Vencimento</th>
            <th scope="col">Valor</th>
            <th scope="col">Pagamento</th>
            <th scope="col" style="width: 7%;"></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($lancamentosfinanceiros as $lancamento)
            <tr>
              <td class="text-truncate">
                <a href="javascript:void(0);">
                  <i class="ri-file-text-line btn-ghost px-2"></i>
                </a> {{ $lancamento->pessoa->nome_razao }}
              </td>
              <td class="text-truncate"> {{ Carbon\Carbon::parse($lancamento->data_emissao)->format('d/m/Y') }} </td>
              <td class="text-truncate"> <input type="text" class="money border-0 bg-transparent" value="{{ $lancamento->valor}}"> </td>
              <td class="text-truncate"> {{ Carbon\Carbon::parse($lancamento->data_pagamento)->format('d/m/Y') }} </td>
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
                      <x-painel.form.delete route="lancamento-financeiro-delete" id="{{ $lancamento->uid }}" />
                    </li>
                  </ul>
                </div>

              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center"> Não há lançamentos na base. </td>
            </tr>
          @endforelse
        </tbody>
      </table>
      <div class="row mt-3">
        {!! $lancamentosfinanceiros->withQueryString()->links('pagination::bootstrap-5') !!}
      </div>
    </div>

  </div>
</div>
