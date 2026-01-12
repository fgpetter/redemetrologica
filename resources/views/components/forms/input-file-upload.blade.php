@props([
    'label',
    'arquivoSalvo' => null,
    'wireModel',
    'campo',
    'pasta' => 'interlab-material',
    'accept' => '.doc,.docx,.pdf',
])

<div>
    <label class="form-label">{{ $label }}</label>
    @if ($arquivoSalvo)
        <div class="d-flex justify-content-between align-items-center">
            <div class="pe-2">
                <a href="{{ asset($pasta . '/' . $arquivoSalvo) }}" target="_blank" class="text-decoration-none">
                    <i class="ph-file-arrow-down align-middle" style="font-size: 1.4rem"></i>
                    {{ $arquivoSalvo }}
                </a>
            </div>
            <button type="button" class="btn btn-sm btn-danger py-0 px-2" 
                wire:click="removerArquivo('{{ $arquivoSalvo }}', '{{ $campo }}')">
                <i class="ph-trash align-middle d-block d-xxl-none" style="font-size: 1rem"></i>
                <span class="d-none d-xxl-block">REMOVER</span>
            </button>
        </div>
    @else
        <input type="file" class="form-control" wire:model="{{ $wireModel }}" id="{{ $wireModel }}" 
            accept="{{ $accept }}">
        <div wire:loading wire:target="{{ $wireModel }}" class="text-muted small mt-1">
            <span class="spinner-border spinner-border-sm" role="status"></span>
            Carregando arquivo...
        </div>
        @error($wireModel) <div class="text-warning">{{ $message }}</div> @enderror
    @endif
</div>

