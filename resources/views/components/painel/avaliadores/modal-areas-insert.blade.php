{{-- modal --}}
<div class="modal fade" id="{{ isset($area) ? 'areaModal'.$area->uid : 'areaModal'}}" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="areaModalLabel">Adicionar Qualificação</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ isset($area) ? route('avaliador-update-area', $area->uid) : route('avaliador-create-area', $avaliador->uid) }}">
            @csrf
            <div class="row gy-3 mb-3">

              <div class="col-12">
                <x-forms.input-select name="area_id" label="Area" required>
                  <option value="">Selecione</option>
                  @foreach ($areasatuacao as $area_atuacao)
                    <option value="{{ $area_atuacao->id }}" @selected( isset($area) && $area->area_id == $area_atuacao->id )>{{ $area_atuacao->descricao }}</option>
                  @endforeach
                </x-forms.input-select>
                @error('area_id') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-8">
                <x-forms.input-select name="situacao" label="Situação">
                  <option value="">Selecione</option>
                  <option value="ATIVO" @selected( isset($area) && $area->situacao == 'ATIVO' )>ATIVO</option>
                  <option value="INATIVO" @selected( isset($area) && $area->situacao == 'INATIVO' )>INATIVO</option>
                  <option value="AVALIADOR" @selected( isset($area) && $area->situacao == 'AVALIADOR' )>AVALIADOR</option>
                  <option value="AVALIADOR EM TREINAMENTO" @selected( isset($area) && $area->situacao == 'AVALIADOR EM TREINAMENTO' )>AVALIADOR EM TREINAMENTO</option>
                  <option value="AVALIADOR LIDER" @selected( isset($area) && $area->situacao == 'AVALIADOR LIDER' )>AVALIADOR LIDER</option>
                  <option value="ESPECIALISTA" @selected( isset($area) && $area->situacao == 'ESPECIALISTA' )>ESPECIALISTA</option>
                </x-forms.input-select>
                @error('situacao') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

  
              <div class="col-4">
                <x-forms.input-field name="data_cadastro" type="date" :value="old('data_cadastro') ?? $area->data_cadastro ?? null" label="Data Cadastro" />
                @error('data_cadastro') <div class="text-warning">{{ $message }}</div> @enderror
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
    