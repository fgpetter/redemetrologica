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
        
        <button class="btn btn-sm btn-success mt-2" 
                data-bs-toggle="modal" 
                data-bs-target="#modalEndereco"
                wire:click="abrirModal">
            <i class="ri-add-line align-bottom me-1"></i>
            Adicionar endereço 
        </button>
    </div>
    
    <div class="card-body px-1">
        <ul class="list-group list-group-flush">
            @forelse ($enderecos as $endereco)
                <div wire:key="{{ $endereco->uid }}" class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        {{ $endereco->info }} <br>
                        {{ $endereco->endereco }}, {{ $endereco->complemento }} <br>
                        {{ $endereco->bairro }}, {{ $endereco->cidade }} <br>
                        {{ $endereco->uf }} - CEP: {{ $endereco->cep }}
                    </div>
                    
                    <div class="d-flex align-items-center gap-2">
                        @if ($pessoa->end_padrao == $endereco->id)
                            <span class="badge bg-primary align-top mt-1">Padrão</span>
                        @endif
                        @if ($pessoa->end_cobranca == $endereco->id)
                            <span class="badge bg-primary align-top mt-1">Cobrança</span>
                        @endif
                        
                        <div class="dropdown">
                            <a href="#" role="button" data-bs-toggle="dropdown">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" 
                                       data-bs-toggle="modal" 
                                       data-bs-target="#modalEndereco"
                                       wire:click="abrirModal('{{ $endereco->uid }}')">
                                        Editar
                                    </a>
                                </li>
                                {{-- Só exibee se não for endereço de PEP --}}
                                @if (!Str::of($endereco->info)->lower()->contains('inscrito no pep:'))
                                    <li>
                                        <x-painel.form-delete.delete 
                                            route="endereco-delete" 
                                            :id="$endereco->uid"
                                            class="dropdown-item" />
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            @empty
                <p class="m-3">Não há endereço cadastrado</p>
            @endforelse
        </ul>
    </div>

    <!-- Modal Único -->
    <div wire:ignore.self class="modal fade" id="modalEndereco" tabindex="-1">
        <div class="modal-dialog modal-dialog-left modal-lg"> 
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $enderecoAtivo ? 'Editar Endereço' : 'Novo Endereço' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <livewire:enderecos.modalform 
                        :pessoa="$pessoa" 
                        :enderecoUid="$enderecoAtivo"
                        key="{{ $enderecoAtivo ?? 'new' }}" />
                </div>
            </div>
        </div>
    </div>
</div>