<div class="col-12">
    <hr class="my-2">
    <h6 class="text-dark">Dados de Cobrança</h6>
</div>

<div class="col-12 text-dark">
    <label class="form-label mb-1">Forma de pagamento <span class="text-danger-emphasis">*</span></label>
    <div class="d-flex flex-wrap gap-4">
        @foreach (\App\Enums\FormaPagamentoCobranca::cases() as $formaPagamento)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="cobranca_forma_pagamento"
                    id="cobranca_forma_pagamento_{{ $formaPagamento->value }}"
                    value="{{ $formaPagamento->value }}"
                    wire:model="dadosCobranca.forma_pagamento">
                <label class="form-check-label" for="cobranca_forma_pagamento_{{ $formaPagamento->value }}">
                    {{ $formaPagamento->label() }}
                </label>
            </div>
        @endforeach
    </div>
    @error('dadosCobranca.forma_pagamento') <span class="text-danger small">{{ $message }}</span> @enderror
</div>

<div class="col-12 text-dark">
    <label class="form-label mb-1">Necessário envio de pedido/ordem de compra ou empenho? <span class="text-danger-emphasis">*</span></label>
    <div class="d-flex gap-4">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="cobranca_exige_pedido_compra"
                id="cobranca_exige_pedido_compra_sim" value="1"
                wire:model="dadosCobranca.exige_pedido_compra">
            <label class="form-check-label" for="cobranca_exige_pedido_compra_sim">Sim</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="cobranca_exige_pedido_compra"
                id="cobranca_exige_pedido_compra_nao" value="0"
                wire:model="dadosCobranca.exige_pedido_compra">
            <label class="form-check-label" for="cobranca_exige_pedido_compra_nao">Não</label>
        </div>
    </div>
    @error('dadosCobranca.exige_pedido_compra') <span class="text-danger small">{{ $message }}</span> @enderror
</div>

<div class="col-12 text-dark">
    <label class="form-label mb-1">É entidade governamental? <span class="text-danger-emphasis">*</span></label>
    <div class="d-flex gap-4">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="cobranca_entidade_governamental"
                id="cobranca_entidade_governamental_sim" value="1"
                wire:model.live="dadosCobranca.entidade_governamental">
            <label class="form-check-label" for="cobranca_entidade_governamental_sim">Sim</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="cobranca_entidade_governamental"
                id="cobranca_entidade_governamental_nao" value="0"
                wire:model.live="dadosCobranca.entidade_governamental">
            <label class="form-check-label" for="cobranca_entidade_governamental_nao">Não</label>
        </div>
    </div>
    @error('dadosCobranca.entidade_governamental') <span class="text-danger small">{{ $message }}</span> @enderror
</div>

@if (($dadosCobranca['entidade_governamental'] ?? null) === '1')
    <div class="col-md-4">
        <x-forms.input-select wire:model="dadosCobranca.esfera_governamental"
            name="cobranca_esfera_governamental" label="Esfera" :required="true">
            <option value="">Selecione</option>
            @foreach (\App\Enums\EsferaGovernamental::cases() as $esfera)
                <option value="{{ $esfera->value }}">{{ $esfera->label() }}</option>
            @endforeach
        </x-forms.input-select>
        @error('dadosCobranca.esfera_governamental') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
@endif
