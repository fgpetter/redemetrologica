{{-- modal --}}
<div class="modal fade" id="{{ isset($status) ? 'avaliadorStatusModal'.$status->uid : 'avaliadorStatusModal'}}" tabindex="-1" aria-labelledby="avaliadorStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="avaliadorStatusModalLabel">Adicionar Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ isset($status) ? route('avaliador-update-status', $status->uid) : route('avaliador-create-status', $avaliador->uid) }}">
          @csrf
          <div class="row gy-3 mb-3">

            <div class="col-4">
              <x-forms.input-field name="data" type="date" :value="old('data') ?? $status->data ?? null" label="Data Cadastro" />
              @error('data') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-8">
              <x-forms.input-select name="status" label="Situação">
                <option value="">Selecione</option>
                <option value="ATIVO" @selected( isset($status) && $status->status == 'ATIVO' )>ATIVO</option>
                <option value="INATIVO" @selected( isset($status) && $status->status == 'INATIVO' )>INATIVO</option>
                <option value="AVALIADOR" @selected( isset($status) && $status->status == 'AVALIADOR' )>AVALIADOR</option>
                <option value="AVALIADOR EM TREINAMENTO" @selected( isset($status) && $status->status == 'AVALIADOR EM TREINAMENTO' )>AVALIADOR EM TREINAMENTO</option>
                <option value="AVALIADOR LIDER" @selected( isset($status) && $status->status == 'AVALIADOR LIDER' )>AVALIADOR LIDER</option>
                <option value="ESPECIALISTA" @selected( isset($status) && $status->status == 'ESPECIALISTA' )>ESPECIALISTA</option>
              </x-forms.input-select>
            </div>

            <div class="col-6">
              <x-forms.input-select name="parecer_positivo" label="Parecer Positivo">
                <option value="0">Não</option>
                <option value="1" @selected(isset($status) && $status->parecer_positivo) >Sim</option>
              </x-forms.input-select>
            </div>

            <div class="col-6">
              <x-forms.input-select name="seminario" label="Participou Seminario">
                <option value="0">Não</option>
                <option value="1" @selected(isset($status) && $status->seminario)>Sim</option>
              </x-forms.input-select>
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
