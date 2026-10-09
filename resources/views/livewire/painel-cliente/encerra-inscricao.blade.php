<div class="mb-3 w-100">
    @if($inscritosCount > 0)
        @include('painel.painel-cliente.partials.tutorial-help', ['items' => [[
            'section' => 'concluir-inscricao',
            'title' => 'Como concluir e conferir minha inscrição?',
        ]]])
    @endif

    <div class="d-flex justify-content-end">
        <button class="btn mt-3 {{ $inscritosCount > 0 ? 'btn-success' : 'btn-danger' }} btn-lg shadow-sm" wire:click="encerrarInscricoes">
            @if($inscritosCount > 0)
                <i class="ri-logout-box-line me-2"></i>
                Concluir Inscrições
            @else
                <i class="ri-close-line me-2"></i>
                Cancelar
            @endif
        </button>
    </div>
</div>
