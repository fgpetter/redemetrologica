@props(['name', 'value' => '', 'rascunho', 'label' => 'Descrição'])
<div class="suporte-editor" wire:ignore
    x-data="suporteEditor({ campo: @js($name), valor: @js($value), uploadUrl: @js(route('suporte-imagem-upload', $rascunho)), csrf: @js(csrf_token()) })">
    <x-forms.input-textarea :name="$name" :label="$label" x-ref="editor">{{ $value }}</x-forms.input-textarea>
    <div class="text-danger small mt-1" x-text="erro" role="alert"></div>
    <small class="text-muted">Imagens inseridas no texto: até 5 MB cada.</small>
</div>
@error($name) <div class="text-danger small">{{ $message }}</div> @enderror
