{{-- modal --}}
<div class="modal fade" id="{{ isset($material) ? 'materialModal'.$material->uid : 'materialModal'}}" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="materialModalLabel">Adicionar Material</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ isset($material) ? route('materiais-padroes-update', $material->uid) : route('materiais-padroes-store') }}">
          @csrf
          <div class="row gy-3">

            <div class="col-9">
              <x-forms.input-field name="descricao" label="Descrição" 
              :value="old('descricao') ?? $material->descricao ?? null" required />
              @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
            </div>


            <div class="col-3">
              <x-forms.input-select name="tipo" label="Tipo" required>
                <option @selected( isset($material) && $material->tipo == "AMBOS" ) value="AMBOS">Ambos</option>
                <option @selected( isset($material) && $material->tipo == "CURSOS" ) value="CURSOS">Cursos</option>
                <option @selected( isset($material) && $material->tipo == "INTERLAB" ) value="INTERLAB">Interlab</option>
              </x-forms.input-select>
              @error('tipo') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <x-forms.input-textarea name="observacoes" label="Observações">{{ old('observacoes') ?? $material->observacoes ?? null }}</x-forms.input-textarea>
              @error('observacoes') <div class="text-warning">{{ $message }}</div> @enderror
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
  