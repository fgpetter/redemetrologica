{{-- modal --}}
<div class="modal fade" id="{{ isset($modalidadepagamento) ? 'modalidade-pagamentoModal'.$modalidadepagamento->uid : 'modalidade-pagamentoModal'}}" 
  tabindex="-1" aria-labelledby="modalidade-pagamentoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalidade-pagamentoModalLabel">Adicionar Modalidade de Pagamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ isset($modalidadepagamento) ? route('modalidade-pagamento-update', $modalidadepagamento->uid) : route('modalidade-pagamento-store') }}">
          @csrf
          <div class="row gy-3">

            <div class="col-12">
              <label for="descricao" class="form-label">Descrição</label>
              <input type="text" class="form-control" id="descricao" name="descricao"
              value="{{ old('descricao') ?? $modalidadepagamento->descricao ?? null }}">
              @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

          </div>
          <div class="modal-footer mt-3">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
{{-- endmodal --}}
  