{{-- modal --}}
<div class="modal fade" id="{{ isset($parametro) ? 'parametroModal'.$parametro->uid : 'parametroModal'}}" tabindex="-1" aria-labelledby="parametroModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="parametroModalLabel">Adicionar Área de Atuação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ isset($parametro) ? route('parametro-update', $parametro->uid) : route('parametro-store') }}">
          @csrf
          <div class="row gy-3">

            <div class="col-12">
              <label for="descricao" class="form-label">Descrição</label>
              <input type="text" class="form-control" id="descricao" name="descricao"
              value="{{ old('descricao') ?? $parametro->descricao ?? null }}">
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
  