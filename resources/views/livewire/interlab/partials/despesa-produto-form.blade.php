<div
    x-data="{
        quantidade: '{{ $produto['quantidade'] }}',
        valor: '{{ $produto['valor'] }}',
        get total() {
            const qtd = parseFloat(this.quantidade.replace(',', '.')) || 0;
            const val = parseFloat(this.valor.replace(',', '.')) || 0;
            if (qtd === 0 || val === 0) return '';
            return (qtd * val).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }"
>
    <div class="row g-2">
        <div class="col-12">
            <label class="form-label mb-0">Material/Serviço <span class="text-danger">*</span></label>
            <input type="text" class="form-control" wire:model="produtos.{{ $index }}.material_servico" required />
            @error("produtos.{$index}.material_servico")
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label mb-0">Fabricante</label>
            <input type="text" class="form-control" wire:model="produtos.{{ $index }}.fabricante" />
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label mb-0">Cod Fabricante</label>
            <input type="text" class="form-control" wire:model="produtos.{{ $index }}.cod_fabricante" />
        </div>
        <div class="col-4">
            <label class="form-label mb-0">Quantidade</label>
            <input type="text" class="form-control" wire:model="produtos.{{ $index }}.quantidade" x-model="quantidade" placeholder="0,00" />
        </div>
        <div class="col-4">
            <label class="form-label mb-0">Valor</label>
            <input type="text" class="form-control" wire:model="produtos.{{ $index }}.valor" x-model="valor" placeholder="0,00" />
        </div>
        <div class="col-4">
            <label class="form-label mb-0">Total</label>
            <input type="text" class="form-control" readonly :value="total" :placeholder="total ? '' : 'Calculado automaticamente'" />
        </div>
        <div class="col-4">
            <label class="form-label mb-0">Lote</label>
            <input type="text" class="form-control" wire:model="produtos.{{ $index }}.lote" />
        </div>
        <div class="col-4">
            <label class="form-label mb-0">Validade</label>
            <input type="date" class="form-control" wire:model="produtos.{{ $index }}.validade" />
        </div>
        <div class="col-4">
            <label class="form-label mb-0">Data da compra</label>
            <input type="date" class="form-control" wire:model="produtos.{{ $index }}.data_compra" />
        </div>
    </div>
</div>
