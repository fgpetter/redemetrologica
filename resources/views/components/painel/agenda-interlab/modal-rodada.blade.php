@props([
  'rodada' => null,
  'agendainterlab' => null,
  'interlabParametros' => null,
])
{{-- modal --}}
<div class="modal fade" id="{{ isset($rodada) ? 'rodadaModal'.$rodada->uid : 'rodadaModal'}}" tabindex="-1" aria-labelledby="rodadaModalLabel" >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rodadaModalLabel">{{ isset($rodada) ? 'Editar Rodada' : 'Adicionar Rodada'}}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row gy-3">
          <form method="POST" action="{{ route('salvar-rodada') }}">
            @csrf
            <input type="hidden" name="agenda_interlab_id" value="{{ $agendainterlab->id }}">
            <input type="hidden" name="rodada_id" value="{{ $rodada?->id }}">
            <div class="row">

              <div class="col-10 py-2">
                <x-forms.input-field name="descricao" label="Descrição" :value="old('descricao') ?? ($rodada->descricao ?? null)" required/>
                @error('descricao') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-2 py-2">
                <x-forms.input-field type="number" name="vias" label="N° de Vias" :value="old('vias') ?? ($rodada->vias ?? null)" required/>
                @error('vias') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
              </div>

              <div class="col-12 m-2 py-2 bg-light rounded">
                <label class="form-label">Selecione os parametros da rodada</label>
                @foreach ($interlabParametros as $parametro)
                    <div class="form-check mb-2">

                        <input class="form-check-input" name="parametros[]" 
                          value="{{ $parametro->parametro->id }}" type="checkbox"
                          @checked( in_array( $parametro->parametro->id, $rodada?->parametros->pluck('parametro_id')->toArray() ?? [] ) )
                          id="{{ 'checkBox'.$rodada?->id.$parametro->parametro->id }}" >

                        <label class="form-check-label" for="{{ 'checkBox'.$rodada?->id.$parametro->parametro->id }}">
                            {{ $parametro->parametro->descricao }}
                        </label>
                    </div>
                @endforeach
              </div>

              <div class="col-12 py-2">
                <x-forms.input-textarea name="cronograma" label="Cronograma">{{ old('cronograma') ?? ($rodada->cronograma ?? null)}}</x-forms.input-textarea>
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
{{-- endmodal --}}
