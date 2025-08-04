
<div>
    <button wire:click="gerarCertificado" wire:loading.attr="disabled" class="dropdown-item">
        <span wire:loading.remove wire:target="gerarCertificado">Enviar Certificado</span>
        <span wire:loading wire:target="gerarCertificado">Enviando...</span>
    </button>
</div>