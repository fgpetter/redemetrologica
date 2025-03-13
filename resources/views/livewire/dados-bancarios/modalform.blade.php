<form wire:submit.prevent="salvar">

    <div class="row mb-4">
        <div class="row gy-2">
            <div class="col-8">
                <x-forms.input-field
                    wire:model="conta.nome_banco"
                    name="nome_banco"
                    label="Nome do banco"
                    placeholder="Ex. Banco do Brasil"
                />
                @error('conta.nome_banco') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            
            <div class="col-4">
                <x-forms.input-field
                    wire:model="conta.cod_banco"
                    name="cod_banco"
                    label="Código do banco"
                />
                @error('conta.cod_banco') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            
            <div class="col-6">
                <x-forms.input-field
                    wire:model="conta.agencia"
                    name="agencia"
                    label="Agência"
                />
                @error('conta.agencia') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            
            <div class="col-6">
                <x-forms.input-field
                    wire:model="conta.conta"
                    name="conta"
                    label="Conta"
                />
                @error('conta.conta') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
    
    <div class="col-lg-12">
        <div class="hstack gap-2 justify-content-end">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Sair</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </div>
</form>