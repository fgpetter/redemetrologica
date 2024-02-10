{{-- modal --}}
<div class="modal fade" id="{{ isset($avaliacao) ? 'tipoAvaliacaoModal'.$avaliacao->uid : 'tipoAvaliacaoModal'}}" tabindex="-1" aria-labelledby="tipoAvaliacaoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tipoAvaliacaoModalLabel">Adicionar Área de Atuação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ isset($avaliacao) ? route('tipo-avaliacao-update', $avaliacao->uid) : route('tipo-avaliacao-store') }}">
          @csrf
          <div class="row gy-3">

            <div class="col-12">
              <label for="descricao" class="form-label">Descrição</label>
              <input type="text" class="form-control" id="descricao" name="descricao"
              value="{{ old('descricao') ?? $avaliacao->descricao ?? null }}">
              @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
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
  