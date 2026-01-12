<div class="col-12">
    <div class="card px-4 py-3 border border-primary shadow-sm mb-4">
        <h5 class="card-subtitle text-primary-emphasis mb-3">
            <i class="ri-flask-line me-2"></i>Dados do Interlaboratorial
        </h5>
        @php
            $interlab = session('interlab');
        @endphp
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2">
                    <strong class="text-muted">Interlaboratorial:</strong><br>
                    <span class="fs-6">{{ $interlab->interlab->nome }}</span>
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-2">
                    <strong class="text-muted">Período:</strong><br>
                    <span class="fs-6">
                        {{ \Carbon\Carbon::parse($interlab->data_inicio)->format('d/m/Y') }} até
                        {{ \Carbon\Carbon::parse($interlab->data_fim)->format('d/m/Y') }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    
    
    <!-- BuscaCNPJ -->
    <div class="mb-4">
        <livewire:painel-cliente.busca-c-n-p-j />
    </div>
    
   
    <div class="accordion accordion-flush" id="mainInscricaoAccordion">
        <style>
            .accordion-button::after {
                display: none !important;
            }
        </style>
        
        <!-- Confirmar CNPJ -->
        <livewire:painel-cliente.confirma-c-n-p-j />


        
        <!-- Laboratórios inscritos -->
        <livewire:painel-cliente.lab-inscritos />

        <!-- Adicionar novo laboratório -->
        <livewire:painel-cliente.novo-lab-inscrito />
        
    </div>
    
</div>
            <div class="col text-s">
                <div class="d-flex justify-content-end align-items-center h-100">
                    <!-- Concluir Inscrição -->
                    <livewire:painel-cliente.encerra-inscricao />
                </div>
            </div>
