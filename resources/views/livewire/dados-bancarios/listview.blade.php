<div class="card shadow-none border overflow-hidden card-border-info ">
    <div class="card-header d-flex justify-content-between align-items-start bg-info-subtle py-2">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm">
                <span class="avatar-title bg-dark-subtle text-dark rounded-circle fs-3">
                    <i class="ph-bank-light"></i>
                </span>
            </div>
            <h4 class="card-title mb-0">Dados bancários</h4>
        </div>
        <button class="btn btn-sm btn-success mt-2" 
                data-bs-toggle="modal" 
                data-bs-target="#modalConta"
                wire:click="abrirModal">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar conta
        </button>
    </div>
    
    <div class="px-1">
        <ul class="list-group list-group-flush">
            @forelse ($contas as $conta)
                <div wire:key="{{ $conta->id }}" class="list-group-item d-flex justify-content-between align-items-center">
                    {{-- {{ $conta->nome_conta }} <br>   nome_conta deveria receber um valor indicando de qual local ela foi adicionada? ex: Fornecedor, avaliador...--}}
                    {{ $conta->nome_banco }}, código: {{ $conta->cod_banco }} <br>
                    Agência:{{ $conta->agencia }}, {{ $conta->conta }}
                    
                    <div>
                        <div class="dropdown">
                            <a href="#" role="button" data-bs-toggle="dropdown">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" 
                                       data-bs-toggle="modal" 
                                       data-bs-target="#modalConta"
                                       wire:click="abrirModal('{{ $conta->uid }}')">
                                        Editar
                                    </a>
                                </li>
                                <li>
                                    <x-painel.form-delete.delete 
                                        route='conta-delete' 
                                        :id="$conta->uid"
                                        class="dropdown-item" />
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @empty
                <p class="m-3">Não há conta cadastrada</p>
            @endforelse
        </ul>
    </div>

    <!-- Modal Único -->
    <div wire:ignore.self class="modal fade" id="modalConta" tabindex="-1">
        <div class="modal-dialog modal-dialog-right modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $contaAtiva ? 'Editar Conta' : 'Nova Conta' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <livewire:dados-bancarios.modalform 
                        :pessoa="$pessoa" 
                        :contaUid="$contaAtiva"
                        key="{{ $contaAtiva ?? 'new' }}" />
                </div>
            </div>
        </div>
    </div>
   
</div>