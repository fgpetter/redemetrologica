<div class="card shadow-none border overflow-hidden card-border-info mt-3">
  <div class="card-header d-flex justify-content-between align-items-start bg-info-subtle py-2">
    <div class="d-flex align-items-center gap-3">
      <div class="avatar-sm">
        <span class="avatar-title bg-dark-subtle text-dark rounded-circle fs-3">
          <i class="ph-bank-light"></i>
        </span>
      </div>
      <h4 class="card-title mb-0">Dados bancários</h4>
    </div>
    
    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_conta_cadastro"
      class="btn btn-sm btn-success mt-2">
      <i class="ri-add-line align-bottom me-1"></i>
      Adicionar conta
    </a>
  </div>
  <div class="card-body px-1">

    <ul class="list-group list-group-flush">
      @forelse ($pessoa->dadosBancarios as $conta)

          <div class="list-group-item d-flex justify-content-between align-items-center">
            {{ $conta->nome_conta }} <br>
            {{ $conta->nome_banco }}, código: {{ $conta->cod_banco }} <br>
            Agência:{{ $conta->agencia }}, {{ $conta->conta }}
            <div>
              <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                  data-bs-toggle="tooltip" title="Detalhes e edição"></i>
              </a>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                <li>
                  <a class="dropdown-item" data-bs-toggle="modal"
                    data-bs-target="{{ "#modal_conta_$conta->uid" }}">Editar</a>
                </li>
                <li>
                  <x-painel.form-delete.delete route='conta-delete' id="{{ $conta->uid }}" />
                </li>
              </ul>
            </div>
          </div>
          <x-painel.dados-bancarios.modal :conta="$conta" :pessoa="$pessoa" />
      @empty
        <p>Não ha conta cadastrada</p>
      @endforelse
    </ul>

    <x-painel.dados-bancarios.modal :pessoa="$pessoa" />
  </div>
</div>
