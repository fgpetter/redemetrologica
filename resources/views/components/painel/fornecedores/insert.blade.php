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
          <x-forms.input-field :value="old('pessoa_contato') ?? ($fornecedor->pessoa_contato ?? null)" name="pessoa_contato" label="Pessoa de Contato" />
            @error('pessoa_contato') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-4">
          <x-forms.input-field :value="old('pessoa_contato_email') ?? ($fornecedor->pessoa_contato_email ?? null)" type="email" name="pessoa_contato_email" label="Email" />
            @error('pessoa_contato_email') <div class="text-warning">{{ $message }}</div> @enderror
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
          <x-forms.input-field :value="old('site') ?? ($fornecedor->pessoa->site ?? null)" name="site" label="Site" />
          @error('site') <div class="text-warning">{{ $message }}</div> @enderror
        </div>
            
        <div class="row mt-3">
          <div class="col-12">
            <label class="form-label">Áreas de Atuação</label>
            <div class="d-flex flex-wrap gap-3">
              
              @foreach (App\Enums\FornecedorArea::cases() as $area)
              <div class="form-check bg-light rounded check-bg" style="padding: 0.65rem 1.8rem 0.65rem;">
                <input type="checkbox" name="fornecedor_area[]" class="form-check-input"
                  value="{{ $area->value }}" 
                  id="fornecedor_area_{{ $area->value }}"
                  @checked(in_array($area->value, old('fornecedor_area', $fornecedor->fornecedor_area ?? [])))>
                <label for="fornecedor_area_{{ $area->value }}">{{ $area->label() }}</label>
              </div>
              @endforeach
            </div>
            @error('fornecedor_area') <div class="text-warning">{{ $message }}</div> @enderror
          </div>
        </div>

        <hr>

        <div class="row">
          <div class="col-4">
            <x-forms.input-field :value="old('fornecedor_area_atuacao') ?? ($fornecedor->fornecedor_area_atuacao ?? null)" name="fornecedor_area_atuacao" label="Área de Atuação" />
            @error('fornecedor_area_atuacao') <div class="text-warning">{{ $message }}</div> @enderror
          </div>
          <div class="col-4">
            <x-forms.input-field :value="old('fornecerdor_desde') ?? ($fornecedor->fornecerdor_desde ?? null)" type="date" name="fornecerdor_desde" label="Data de Início" />
            @error('fornecerdor_desde') <div class="text-warning">{{ $message }}</div> @enderror
          </div>
          <div class="col-4">
            <x-forms.input-select name='ativo' label='ATIVO'>
              <option value="1" @selected($fornecedor->ativo == 1)>SIM</option>
              <option value="0" @selected($fornecedor->ativo == 0)>NÃO</option>
            </x-forms.input-select>
            @error('ativo') <div class="text-warning">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="row mt-3">
          <div class="col">
            <x-forms.input-textarea name="observacoes" label="Observações" rows="3">{{ old('observacoes') ?? ($fornecedor->observacoes ?? null) }}</x-forms.input-textarea>
            @error('observacoes') <div class="text-warning">{{ $message }}</div> @enderror
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
