@props([
"despesa" => null,
"planosconta" => App\Models\PlanoConta::all(),
"agendacurso" => null
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
          <form method="POST" action="{{ route('salvar-despesa', $despesa?->id) }}">
            @csrf
            @if (isset($despesa))
              <input type="hidden" name="despesa_id" value="{{ $despesa?->id }}">
            @endif
            <input type="hidden" name="agenda_curso_id" value="{{ $agendacurso->id }}">
            <div class="row gy-1">
              <div class="col-12">
                <x-forms.input-select name="plano_conta" label="Descrição" required="true">
                  <option>- Selecione</option>
                  @foreach ($planosconta as $planoconta)
                    <option value="{{ $planoconta->id }}" @selected($planoconta->id == $despesa?->plano_conta_id)>{{ $planoconta->descricao }}</option>
                  @endforeach
                </x-forms.input-select>
                @error('plano_conta')
                  <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
              </div>

              <div class="col-4">
                <label for="quantidade" class="form-label">Qauntidade</label>
                <input type="number" class="form-control" name="quantidade" 
                  value="{{old('quantidade') ?? ($despesa->quantidade ?? null)}}" 
                  id="{{'despesa_qtd'.$despesa?->id}}" required>
                @error('quantidade')
                  <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-4">
                <label for="valor" class="form-label">Valor</label>
                <input type="text" class="form-control money" name="valor" 
                  value="{{old('valor') ?? ($despesa->valor ?? null)}}" 
                  id="{{'despesa_valor'.$despesa?->id}}" required>
                @error('valor')
                  <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
              </div>
              <div class="col-4">
                <label for="total" class="form-label">Total</label>
                <input type="text" class="form-control money" name="total" 
                  value="{{old('total') ?? ($despesa->total ?? null)}}" 
                  id="{{'despesa_total'.$despesa?->id}}" required>
                @error('total')
                  <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
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
  const valor{{$despesa?->id}} = document.querySelector("{{'#despesa_valor'.$despesa?->id}}")
  const qtd{{ $despesa?->id}} = document.querySelector("{{'#despesa_qtd'.$despesa?->id}}")
  const total{{$despesa?->id}} = document.querySelector("{{'#despesa_total'.$despesa?->id}}")
  
  valor{{$despesa?->id}}.addEventListener('keyup', () => {
    total{{$despesa?->id}}.value = (qtd{{ $despesa?->id}}.value * valor{{$despesa?->id}}.value.replace(".", "").replace(",", ".")).toFixed(2);
  });
  
  qtd{{ $despesa?->id}}.addEventListener('keyup', () => {
    total{{$despesa?->id}}.value = (qtd{{ $despesa?->id}}.value * valor{{$despesa?->id}}.value.replace(".", "").replace(",", ".")).toFixed(2);
  });
</script>
{{-- endmodal --}}
