
<form wire:submit.prevent="salvar">
    <div class="row gy-2">
        <input type="hidden" wire:model="endereco.pessoa_id">

        <div class="col-12">
            <x-forms.input-field wire:model.lazy="endereco.info" name="endereco.info"
                label="Nome do endereço" placeholder="Ex. Filial Caxias" />
            @error('endereco.info')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-5 col-sm-4">
            <x-forms.input-field wire:model.lazy="endereco.cep" wire:keyup.debounce.500ms="buscaCep"
                name="endereco.cep" label="CEP" class="cep" required />
            @error('endereco.cep')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-8">
            <x-forms.input-field wire:model.lazy="endereco.endereco" name="endereco.endereco"
                label="Endereço com número" placeholder="Ex. Av. Brasil, 1234" required />
            @error('endereco.endereco')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-6">
            <x-forms.input-field wire:model.lazy="endereco.complemento" name="endereco.complemento"
                label="Complemento" placeholder="Ex. Sala 101" />
            @error('endereco.complemento')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-6">
            <x-forms.input-field wire:model.lazy="endereco.bairro" name="endereco.bairro"
                label="Bairro" />
            @error('endereco.bairro')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-sm-6">
            <x-forms.input-field wire:model.lazy="endereco.cidade" name="endereco.cidade" label="Cidade"
                required />
            @error('endereco.cidade')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-4 col-sm-2">
            <label for="uf" class="form-label mb-0">UF<small class="text-danger"> *</small></label>
            <input type="text" class="form-control" wire:model.lazy="endereco.uf" name="endereco.uf"
                id="uf" maxlength="2" pattern="[A-Z]{2}" title="Duas letras maiúsculas" required
                oninput="this.value = this.value.toUpperCase()">
            @error('endereco.uf')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-6 col-sm-4">
            <x-forms.input-check-pill wire:model.lazy="endereco.end_padrao" name="endereco.end_padrao"
                label="Endereço Padrão" />
            @error('endereco.end_padrao')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-12 mt-4">
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Sair</button>
                <button type="submit" class="btn {{ $saved ? 'btn-success' : 'btn-primary' }}">
                    {{ $saved ? 'Salvo' : 'Salvar' }}
                </button>
            </div>
        </div>
    </div>
</form>

@script
    <script>
        $wire.on('refresh-enderecos-list', () => {
            setTimeout(() => {
                const modals = document.querySelectorAll('.modal.show');
                modals.forEach(modal => { modal.querySelector('.btn-close').click() })
            }, 1000)
        })
    </script>
@endscript
