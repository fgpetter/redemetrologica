@props([
    'class' => null,
    'pessoa' => null,
])

<div class="card shadow-none border overflow-hidden card-border-info mt-3">
    <div class="card-header d-flex justify-content-between align-items-start bg-info-subtle py-2">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm">
                <span class="avatar-title bg-dark-subtle text-dark rounded-circle fs-3">
                    <i class="ph-map-trifold-light"></i>
                </span>
            </div>
            <h4 class="card-title mb-0">Dados de Endereço</h4>
        </div>
        
        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_endereco_cadastro"
            class="btn btn-sm btn-success mt-2">
            <i class="ri-add-line align-bottom me-1"></i>
            Adicionar endereço
        </a>
    </div>
    <div class="card-body px-1">

        <ul class="list-group list-group-flush">
            @forelse ($pessoa->enderecos as $endereco)
                @if (!$endereco->unidade_id)
                    {{-- Lista somente endereços sem unidade atrelada --}}
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $endereco->endereco }}, {{ $endereco->complemento }} <br>
                        {{ $endereco->bairro }}, {{ $endereco->cidade }} <br>
                        {{ $endereco->uf }} - CEP: {{ $endereco->cep }}
                        <div>
                            @if ($pessoa->end_padrao == $endereco->id)
                                <span class="badge bg-primary align-top mt-1">Padrão</span>
                            @endif
                            @if ($pessoa->end_cobranca == $endereco->id)
                                <span class="badge bg-primary align-top mt-1">Cobrança</span>
                            @endif
                            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                    data-bs-toggle="tooltip" title="Detalhes e edição"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                <li>
                                    <a class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="{{ "#modal_endereco_$endereco->uid" }}">Editar</a>
                                </li>
                                <li>
                                    <x-painel.form-delete.delete route='endereco-delete' id="{{ $endereco->uid }}" />
                                </li>
                            </ul>
                        </div>
                    </div>
                    <x-painel.enderecos.modal :endereco="$endereco" :pessoa="$pessoa" />
                @endif
            @empty
                <p>Não há endereço cadastrado</p>
            @endforelse
        </ul>

        <x-painel.enderecos.modal :pessoa="$pessoa" />
    </div>
</div>
