@props([
"despesa" => null,
"materiaisPadrao" => null,
"agendainterlab" => null,
"fornecedores" => null,
"fabricantes" => null,
])
{{-- modal --}}
<div class="modal fade" id="{{ isset($despesa) ? 'despesaModal'.$despesa->id : 'despesaModal'}}" tabindex="-1" aria-labelledby="despesaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="despesaModalLabel">{{ isset($despesa) ? 'Editar Despesa' : 'Adicionar Despesa'}}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row gy-3">
          <form method="POST" action="{{ route('salvar-despesa') }}">
            @csrf
            <input type="hidden" name="agenda_interlab_id" value="{{ $agendainterlab->id }}">
            <input type="hidden" name="despesa_id" value="{{ $despesa?->id }}">
            <div class="row gy-2">

              <div class="col-12">
                <x-forms.input-select name="material_padrao" label="Descrição" required="true" errorBag="despesas">
                  <option>- Selecione</option>
                  @foreach ($materiaisPadrao as $materialpadrao)
                    <option value="{{ $materialpadrao->id }}" 
                      @selected($materialpadrao->id == $despesa?->material_padrao_id)>{{ $materialpadrao->descricao }}</option>
                  @endforeach
                </x-forms.input-select>
              </div>

              <div class="col-4">
                <label for="fornecedor" class="form-label">Fornecedor</label>
                <input class="form-control" name="fornecedor" 
                  value="{{old('fornecedor') ?? ($despesa->fornecedor ?? null)}}" 
                  list="fornecedorList">
                @error('fornecedor','despesas') <span class="text-warning" role="alert">{{ $message }}</span> @enderror
                <datalist id="fornecedorList">
                  @foreach ($fornecedores as $fornecedor)
                      <option value="{{ $fornecedor->fornecedor }}">
                  @endforeach
              </datalist>

              </div>

              <div class="col-4">
                <label for="fabricante" class="form-label">Fabricante</label>
                <input class="form-control" name="fabricante" 
                  value="{{old('fabricante') ?? ($despesa->fabricante ?? null)}}"
                  list="fabricanteList">
                @error('fabricante','despesas') <span class="text-warning" role="alert">{{ $message }}</span> @enderror

                <datalist id="fabricanteList">
                  @foreach ($fabricantes as $fabricante)
                      <option value="{{ $fabricante->fabricante }}">
                  @endforeach
              </datalist>

              </div>

              <div class="col-4">
                <label for="cod_fabricante" class="form-label">Codigo Fabricante</label>
                <input class="form-control" name="cod_fabricante" 
                  value="{{old('cod_fabricante') ?? ($despesa->cod_fabricante ?? null)}}" >
                @error('cod_fabricante','despesas') <span class="text-warning" role="alert">{{ $message }}</span> @enderror
              </div>


              <div class="col-4">
                <label for="quantidade" class="form-label">Qauntidade</label>
                <input type="number" step=".01" class="form-control" name="quantidade" 
                  value="{{old('quantidade') ?? ($despesa->quantidade ?? null)}}" 
                  id="{{'material_qtd'.$despesa?->id}}" >
                @error('quantidade','despesas') <span class="text-warning" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-4">
                <label for="valor" class="form-label">Valor</label>
                <input type="text" class="form-control money" name="valor" 
                  value="{{old('valor') ?? ($despesa->valor ?? null)}}" 
                  id="{{'material_valor'.$despesa?->id}}" >
                @error('valor','despesas') <span class="text-warning" role="alert">{{ $message }}</span> @enderror
              </div>
              
              <div class="col-4">
                <label for="total" class="form-label">Total</label>
                <input type="text" class="form-control money" name="total" 
                  value="{{old('total') ?? ($despesa->total ?? null)}}" 
                  id="{{'material_total'.$despesa?->id}}" readonly>
                @error('total','despesas') <span class="text-warning" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-4">
                <x-forms.input-field name="lote" label="Lote" :value="old('lote') ?? ($despesa->lote ?? null)"/>
                @error('lote','despesas') <span class="text-warning" role="alert">{{ $message }}</span> @enderror
              </div>
              
              <div class="col-4">
                <x-forms.input-field type="date" name="validade" label="Validade" :value="old('validade') ?? ($despesa?->validade?->format('Y-m-d') ?? null)"/>
                @error('validade','despesas') <span class="text-warning" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-4">
                <x-forms.input-field type="date" name="data_compra" label="Data da compra" :value="old('data_compra') ?? ($despesa?->data_compra?->format('Y-m-d') ?? null)"/>
                @error('data_compra','despesas') <span class="text-warning" role="alert">{{ $message }}</span> @enderror
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
  const valor{{$despesa?->id}} = document.querySelector("{{'#material_valor'.$despesa?->id}}")
  const qtd{{ $despesa?->id}} = document.querySelector("{{'#material_qtd'.$despesa?->id}}")
  const total{{$despesa?->id}} = document.querySelector("{{'#material_total'.$despesa?->id}}")
  
  valor{{$despesa?->id}}.addEventListener('keyup', () => {
    total{{$despesa?->id}}.value = (qtd{{ $despesa?->id}}.value * valor{{$despesa?->id}}.value.replace(".", "").replace(",", ".")).toFixed(2)
  })
  
  qtd{{ $despesa?->id}}.addEventListener('keyup', () => {
    total{{$despesa?->id}}.value = (qtd{{$despesa?->id}}.value * valor{{$despesa?->id}}.value.replace(".", "").replace(",", ".")).toFixed(2)
  })
</script>

{{-- endmodal --}}
