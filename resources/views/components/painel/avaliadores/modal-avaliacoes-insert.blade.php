{{-- modal --}}
<div class="modal fade" id="{{ isset($avaliacao) ? 'avaliacaoModal'.$avaliacao->uid : 'avaliacaoModal'}}" tabindex="-1" aria-labelledby="avaliacaoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="avaliacaoModalLabel">Adicionar Avaliacao</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ isset($avaliacao) ? route('avaliador-update-avaliacao', $avaliacao->uid) : route('avaliador-create-avaliacao', $avaliador->uid) }}">
            @csrf
            <div class="row gy-3 mb-3">
  
              <div class="col-12">
                <label for="empresa" class="form-label">Empresa</label>
                <select class="form-select" name="empresa" aria-label="">
                    <option >Selecione uma empresa</option>
                    <option value="1">One</option>
                    <option value="2">Two</option>
                    <option value="3">Three</option>
                  </select>
                @error('empresa') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
              
              <div class="col-8">
                <label for="situacao" class="form-label">Situação</label>
                <select class="form-select" name="situacao" aria-label="">
                    <option >Selecione uma situação</option>
                    <option value='AVALIADOR'>AVALIADOR</option>
                    <option value='AVALIADOR EM TREINAMENTO'>AVALIADOR EM TREINAMENTO</option>
                    <option value='AVALIADOR LÍDER'>AVALIADOR LÍDER</option>
                    <option value='ESPECIALISTA'>ESPECIALISTA</option>
                  </select>
                @error('situacao') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
  
              <div class="col-4">
                <label for="data" class="form-label">Data</label>
                <input type="date" class="form-control" name="data" id="data" 
                value="{{ old('data') ?? $avaliacao->data ?? null }}" required>
                    @error('data') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              @if (isset($avaliacao->agenda_avaliacao_id))
                <div class="col-12">
                  <p>
                    <strong>Avaliação realizada:</strong><br>
                    DIMENLAB - LABORATÓRIO DE METROLOGIA LTDA. - 03/02/2014 <br>
                    <a href="#"> Mais informações </a>
                  </p>
                </div>
              @else
              <div class="col-12">
                <p>
                  <strong>Adicionado manualmente por:</strong><br>
                  Filipe Petter - 03/02/2024 <br>
                </p>
              </div>
              @endif
  
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
    