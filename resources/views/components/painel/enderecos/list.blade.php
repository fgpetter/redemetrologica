@props([
    'class' => null,
    'pessoa' => null,
])

@if (session('error'))
    <x-alerts.alert type="error" />
@endif
@if (session('success'))
    <x-alerts.alert type="sucess" />
@endif

<div class="card {{ $class }}">
    <div class="card-header d-flex justify-content-between">
        <h4 class="card-title mb-0">Dados de Endereço</h4>
        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_endereco_cadastro"
            class="btn btn-sm btn-success">
            <i class="ri-add-line align-bottom me-1"></i>
            Adicionar endereço
        </a>
    </div><!-- end card header -->
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
                                @if ($pessoa->end_padrao != $endereco->id)
                                    {{-- Impede deletar endereço padrão --}}

                                    <x-painel.form-delete.delete route='endereco-delete' id="{{ $endereco->uid }}" />
                                @endif
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
