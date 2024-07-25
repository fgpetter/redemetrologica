@props([
"interlabMaterialPadrao" => null,
"materiaisPadrao" => null,
"agendainterlab" => null
])
{{-- modal --}}
<div class="modal fade" id="{{ isset($interlabMaterialPadrao) ? 'materialPadraoModal'.$interlabMaterialPadrao->id : 'materialPadraoModal'}}" tabindex="-1" aria-labelledby="materialPadraoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="materialPadraoModalLabel">{{ isset($interlabMaterialPadrao) ? 'Editar Material' : 'Adicionar Material'}}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row gy-3">
          <form method="POST" action="{{ route('salvar-material-padrao') }}">
            @csrf
            <input type="hidden" name="agenda_interlab_id" value="{{ $agendainterlab->id }}">
            <div class="row gy-1">

              <div class="col-12">
                <x-forms.input-select name="material_padrao" label="Descrição" required="true">
                  <option>- Selecione</option>
                  @foreach ($materiaisPadrao as $materialpadrao)
                    <option value="{{ $materialpadrao->id }}" 
                      @selected($materialpadrao->id == $interlabMaterialPadrao?->material_padrao_id)>{{ $materialpadrao->descricao }}</option>
                  @endforeach
                </x-forms.input-select>
                @error('material_padrao') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-4">
                <label for="quantidade" class="form-label">Qauntidade</label>
                <input type="number" step=".01" class="form-control" name="quantidade" 
                  value="{{old('quantidade') ?? ($interlabMaterialPadrao->quantidade ?? null)}}" 
                  id="{{'material_qtd'.$interlabMaterialPadrao?->id}}" required>
                @error('quantidade') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-4">
                <label for="valor" class="form-label">Valor</label>
                <input type="text" class="form-control money" name="valor" 
                  value="{{old('valor') ?? ($interlabMaterialPadrao->valor ?? null)}}" 
                  id="{{'material_valor'.$interlabMaterialPadrao?->id}}" required>
                @error('valor') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>
              
              <div class="col-4">
                <label for="total" class="form-label">Total</label>
                <input type="text" class="form-control money" name="total" 
                  value="{{old('total') ?? ($interlabMaterialPadrao->total ?? null)}}" 
                  id="{{'material_total'.$interlabMaterialPadrao?->id}}" readonly>
                @error('total') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-4">
                <x-forms.input-field name="lote" label="Lote" :value="old('lote') ?? ($interlabMaterialPadrao->lote ?? null)"/>
                @error('total') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>
              
              <div class="col-4">
                <x-forms.input-field type="date" name="validade" label="Validade" :value="old('validade') ?? ($interlabMaterialPadrao?->validade->format('Y-m-d') ?? null)"/>
                @error('total') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-4">
                <x-forms.input-field type="date" name="data_compra" label="Data da compra" :value="old('data_compra') ?? ($interlabMaterialPadrao?->data_compra->format('Y-m-d') ?? null)"/>
                @error('total') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>

            </div>
            <div class="modal-footer my-2">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
              <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </form>

        </div>

      </div>
    </div>
  </div>
</div>
{{-- é feio mas funciona --}}
<script defer>
  const valor{{$interlabMaterialPadrao?->id}} = document.querySelector("{{'#material_valor'.$interlabMaterialPadrao?->id}}")
  const qtd{{ $interlabMaterialPadrao?->id}} = document.querySelector("{{'#material_qtd'.$interlabMaterialPadrao?->id}}")
  const total{{$interlabMaterialPadrao?->id}} = document.querySelector("{{'#material_total'.$interlabMaterialPadrao?->id}}")
  
  valor{{$interlabMaterialPadrao?->id}}.addEventListener('keyup', () => {
    total{{$interlabMaterialPadrao?->id}}.value = (qtd{{ $interlabMaterialPadrao?->id}}.value * valor{{$interlabMaterialPadrao?->id}}.value.replace(".", "").replace(",", ".")).toFixed(2)
  })
  
  qtd{{ $interlabMaterialPadrao?->id}}.addEventListener('keyup', () => {
    total{{$interlabMaterialPadrao?->id}}.value = (qtd{{ $interlabMaterialPadrao?->id}}.value * valor{{$interlabMaterialPadrao?->id}}.value.replace(".", "").replace(",", ".")).toFixed(2)
  })
</script>

{{-- endmodal --}}
