<div class="d-grid gap-2 col-lg-6 col-md-8">
    <button wire:click="gerarCartaReconhecimento" class="btn btn-info">Gerar Carta Reconhecimento</button>
    <button wire:click="gerarCartaMarcacao" class="btn btn-info">Gerar Carta de Marcação</button>
</div>
@script
<script>
    $wire.on('show-error-alert', (event) => {
        Swal.mixin({ toast: true, position: 'top-right', iconColor: 'white',
            customClass: { popup: 'colored-toast' }, showConfirmButton: false,
            timer: 6000, timerProgressBar: true, showCloseButton: true
        }).fire({ icon: 'error', title: event.message });
    });
</script>
@endscript
