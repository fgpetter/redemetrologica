
{{-- modal --}}
<div class="modal fade" id="{{ isset($qualificacao) ? 'qualificacaoModal'.$qualificacao->uid : 'qualificacaoModal'}}" tabindex="-1" aria-labelledby="qualificacaoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="qualificacaoModalLabel">Adicionar Qualificação</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ isset($qualificacao) ? route('avaliador-update-qualificacao', $qualificacao->uid) : route('avaliador-create-qualificacao', $avaliador->uid) }}">
            @csrf
            <div class="row gy-3 mb-3">
  
              <div class="col-3">
                <x-forms.input-field name="ano" :value="old('ano') ?? $qualificacao->ano ?? null" label="Ano" />
                @error('ano') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
              
              <div class="col-9">
                <x-forms.input-field name="atividade" 
                    :value="old('atividade') ?? $qualificacao->atividade ?? null" 
                    label="Atividade"
                    list="atividadeList"
                />
                <datalist id="atividadeList">
                    @foreach ($qualificacoeslist['atividades'] as $atividade)
                        <option value="{{ $atividade->atividade }}">
                    @endforeach
                </datalist>
                @error('atividade') <div class="text-warning">{{ $message }}</div> @enderror
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
    