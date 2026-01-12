<div class="card">
  <div class="card-body">
    <form method="POST"
      action="{{ isset($fornecedor->uid) ? route('fornecedor-update', $fornecedor->uid) : route('fornecedor-create') }}"
      enctype="multipart/form-data">
      @csrf
      <div class="row gy-3">

        <div class="col-8">
          <x-forms.input-field :value="old('nome_razao') ?? ($fornecedor->pessoa->nome_razao ?? null)" name="nome_razao" label="Razão Social" />
            @error('nome_razao') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <x-forms.input-field :value="old('cpf_cnpj') ?? ($fornecedor->pessoa->cpf_cnpj ?? null)" name="cpf_cnpj" label="CNPJ/CPF" />
          @error('cpf_cnpj') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <x-forms.input-field :value="old('rg_ie') ?? ($fornecedor->pessoa->rg_ie ?? null)" name="rg_ie" label="Inscrição Estadual" />
          @error('rg_ie') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <x-forms.input-field :value="old('telefone') ?? ($fornecedor->pessoa->telefone ?? null)" name="telefone" mask="telefone" label="Telefone" />
            @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror  
        </div>
        
        <div class="col-4">
          <x-forms.input-field :value="old('telefone_alt') ?? ($fornecedor->pessoa->telefone_alt ?? null)" name="telefone_alt" mask="telefone" label="Telefone Alternativo" />
          @error('telefone_alt') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <x-forms.input-field :value="old('celular') ?? ($fornecedor->pessoa->celular ?? null)" name="celular" mask="telefone" label="Celular" />
          @error('celular') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <x-forms.input-field :value="old('email') ?? ($fornecedor->pessoa->email ?? null)" type="email" name="email" label="Email" />
          @error('email') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <x-forms.input-field :value="old('site') ?? ($fornecedor->pessoa->site ?? null)" type="site" name="site" label="Site" />
          @error('site') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="row mt-3">
          <div class="col">
            <label for="oservacoes" class="form-label">Observações</label>
            <textarea class="form-control" name="oservacoes" id="oservacoes" rows="3"></textarea>
          </div>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary px-4">{{ $fornecedor->id ? 'Atualizar' : 'Salvar' }}</button>
        </div>

      </div>
    </form>
    @if ($fornecedor->id)
      <x-painel.form-delete.delete route="fornecedor-delete" id="{{ $fornecedor->uid }}" label="Fornecedor" />
    @endif



  </div>

</div>
