{{-- modal --}}
<div class="modal fade" id="{{ isset($planoconta) ? 'planocontaModal'.$planoconta->uid : 'planocontaModal'}}" 
  tabindex="-1" aria-labelledby="planocontaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="planocontaModalLabel">Adicionar Plano de Conta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ isset($planoconta) ? route('plano-conta-update', $planoconta->uid) : route('plano-conta-store') }}">
          @csrf
          <div class="row gy-3">

            <div class="col-6">
              <label for="descricao" class="form-label">Descrição</label>
              <input type="text" class="form-control" id="descricao" name="descricao"
              value="{{ old('descricao') ?? $planoconta->descricao ?? null }}">
              @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-3">
              <label for="codigo_contabil" class="form-label">COD Contábil</label>
              <input type="number" class="form-control" id="codigo_contabil" name="codigo_contabil" maxlength="19"
              value="{{ old('codigo_contabil') ?? $planoconta->codigo_contabil ?? null }}">
              @error('codigo_contabil') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-3">
              <label for="grupo_contas" class="form-label">Grupo de Contas</label>
              <input type="text" class="form-control" id="grupo_contas" name="grupo_contas"
              value="{{ old('grupo_contas') ?? $planoconta->grupo_contas ?? null }}">
              @error('grupo_contas') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            <div class="col-6">
              
              <x-forms.input-select name="centro_custo_id" label="Centro de custos">
                <option value="">Selecione um centro de custo</option>
                @if (isset($planoconta) && isset($planoconta->centrocusto))
                <option selected value="{{ $planoconta->centro_custo_id }}">{{ $planoconta->centrocusto->descricao }}</option>
                @endif
                @foreach ($centrocustos as $centrocusto)
                @if (!isset($planoconta) || $planoconta->centro_custo_id <> $centrocusto->id)
                  <option value="{{ $centrocusto->id }}">
                    {{ $centrocusto->descricao }}
                  </option>
                  @endif
                  @endforeach
              </x-forms.input-select>
              
              @error('centro_custo_id') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
{{-- endmodal --}}
  