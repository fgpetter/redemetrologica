<div class="card">
  <div class="card-body">
    @php $pessoa = $fornecedor->pessoa; @endphp
    <div class="card mb-3 shadow-none bg-light">
      <div class="card-header d-flex justify-content-between align-items-center bg-light">
        <h5 class="mb-0">Dados do Fornecedor</h5>
        <a href="{{ route('pessoa-insert', ['pessoa' => $pessoa->uid]) }}" class="btn btn-sm btn-outline-primary">Editar pessoa</a>
      </div>
      <div class="card-body">
          Nome / Razão Social: <span class="fw-bold">{{ $pessoa->nome_razao ?? '' }}</span> <br>
          Nome fantasia: {{ $pessoa->nome_fantasia ?? '' }}<br>
          CPF/CNPJ: {{ $pessoa->cpf_cnpj ?? '' }}<br>
          RG ou Inscrição Estadual: {{ $pessoa->rg_ie ?? '' }}<br>
          Inscrição Municipal: {{ $pessoa->insc_municipal ?? '' }}<br>
          Telefone: {{ $pessoa->telefone ?? '' }}<br>
          Telefone Alternativo: {{ $pessoa->telefone_alt ?? '' }}<br>
          Celular: {{ $pessoa->celular ?? '' }}<br>
          Email: {{ $pessoa->email ?? '' }}<br>
          Site: {{ $pessoa->site ?? '' }}<br>
      </div>
    </div>

    <form method="POST" action="{{ route('fornecedor-update', $fornecedor->uid) }}" enctype="multipart/form-data"
      x-data="{
        areasSelected: {
          @foreach (App\Enums\FornecedorArea::cases() as $area)
            '{{ $area->value }}': {{ $fornecedor->areas->contains(fn($a) => ($a->getRawOriginal('area') ?? $a->area?->value) === $area->value) ? 'true' : 'false' }},
          @endforeach
        }
      }">
      @csrf
      <div class="row gy-3">

        <div class="col-12">
          <label class="form-label">Áreas de Atuação</label>
          <div class="d-flex flex-column gap-2">
            @foreach (App\Enums\FornecedorArea::cases() as $area)
              @php $areaData = $fornecedor->areas->firstWhere(fn($a) => ($a->getRawOriginal('area') ?? $a->area?->value) === $area->value); @endphp
              <div class="card shadow-none bg-light">
                <div class="card-body">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                      id="area-{{ $area->value }}"
                      x-model="areasSelected['{{ $area->value }}']"
                      name="areas[]" value="{{ $area->value }}"
                      @checked($fornecedor->areas->contains(fn($a) => ($a->getRawOriginal('area') ?? $a->area?->value) === $area->value))>
                    <label class="form-check-label" for="area-{{ $area->value }}">{{ $area->label() }}</label>
                  </div>
                  <div class="mt-3" x-show="areasSelected['{{ $area->value }}']" x-collapse>
                    <hr class="my-3">
                    <div class="row gy-3">
                      <div class="col-md-6">
                        <x-forms.input-field
                          name="areas_data[{{ $area->value }}][atuacao]"
                          :value="old('areas_data.'.$area->value.'.atuacao', $areaData?->atuacao)"
                          label="Atuação" />
                      </div>
                      <div class="col-md-6">
                        <x-forms.input-field
                          name="areas_data[{{ $area->value }}][pessoa_contato]"
                          :value="old('areas_data.'.$area->value.'.pessoa_contato', $areaData?->pessoa_contato)"
                          label="Pessoa de Contato" />
                      </div>
                      <div class="col-md-6">
                        <x-forms.input-field
                          name="areas_data[{{ $area->value }}][pessoa_contato_email]"
                          type="email"
                          :value="old('areas_data.'.$area->value.'.pessoa_contato_email', $areaData?->pessoa_contato_email)"
                          label="E-mail" />
                      </div>
                      <div class="col-md-6">
                        <x-forms.input-field
                          name="areas_data[{{ $area->value }}][pessoa_contato_telefone]"
                          :value="old('areas_data.'.$area->value.'.pessoa_contato_telefone', $areaData?->pessoa_contato_telefone)"
                          label="Telefone" mask="telefone" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <hr>

        <div class="row">
          <div class="col-4">
            <x-forms.input-field :value="old('fornecedor_desde', $fornecedor->fornecedor_desde)" type="date" name="fornecedor_desde" label="Data de Início" />
            @error('fornecedor_desde') <div class="text-warning">{{ $message }}</div> @enderror
          </div>
          <div class="col-4">
            <x-forms.input-select name="ativo" label="ATIVO">
              <option value="1" @selected(old('ativo', $fornecedor->ativo) == 1)>SIM</option>
              <option value="0" @selected(old('ativo', $fornecedor->ativo) == 0)>NÃO</option>
            </x-forms.input-select>
            @error('ativo') <div class="text-warning">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="row mt-3">
          <div class="col">
            <x-forms.input-textarea name="observacoes" label="Observações" rows="3">{{ old('observacoes', $fornecedor->observacoes) }}</x-forms.input-textarea>
            @error('observacoes') <div class="text-warning">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary px-4">Atualizar</button>
        </div>

      </div>
    </form>

    @if ($fornecedor->id)
      <x-painel.form-delete.delete route="fornecedor-delete" id="{{ $fornecedor->uid }}" label="Fornecedor" />
    @endif

  </div>
</div>
