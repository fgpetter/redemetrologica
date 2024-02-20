{{-- modal --}}
<div class="modal fade" id="{{ isset($banco) ? 'bancoModal'.$banco->uid : 'bancoModal'}}" tabindex="-1" aria-labelledby="bancoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bancoModalLabel">Adicionar Banco</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ isset($banco) ? route('banco-update', $banco->uid) : route('banco-store') }}">
          @csrf
          <div class="row gy-3">

            <div class="col-3">
              <label for="numero_banco" class="form-label">Número do Banco</label>
              <input type="text" class="form-control" id="numero_banco" name="numero_banco"
              value="{{ old('numero_banco') ?? $banco->numero_banco ?? null }}">
              @error('numero_banco') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-5">
              <label for="nome_banco" class="form-label">Nome do Banco</label>
              <input type="text" class="form-control" id="nome_banco" name="nome_banco"
              value="{{ old('nome_banco') ?? $banco->nome_banco ?? null }}">
              @error('nome_banco') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-3">
              <label for="agencia" class="form-label">Agência</label>
              <input type="text" class="form-control" id="agencia" name="agencia"
              value="{{ old('agencia') ?? $banco->agencia ?? null }}">
              @error('agencia') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-3">
              <label for="conta" class="form-label">Conta</label>
              <input type="text" class="form-control" id="conta" name="conta"
              value="{{ old('conta') ?? $banco->conta ?? null }}">
              @error('conta') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-4">
              <label class="form-label">&nbsp;</label>
              <div class="form-check mb-3 bg-light rounded" style="padding: 0.65rem 1.8rem 0.65rem;">
                <input class="form-check-input" id="movimenta_financeiro" name="movimenta_financeiro" value="1" type="checkbox"
                @checked(old('movimenta_financeiro') ?? ($banco->movimenta_financeiro) ?? null) >
                <label class="form-check-label" for="movimenta_financeiro">
                  MOVIMENTA FINANCEIRO
                </label>
              </div>
            </div>

            <div class="col-4">
              <label class="form-label">&nbsp;</label>
              <div class="form-check mb-3 bg-light rounded" style="padding: 0.65rem 1.8rem 0.65rem;">
                <input class="form-check-input" id="padrao" name="padrao" value="1" value="1" type="checkbox"
                @checked(old('padrao') ?? ($banco->padrao) ?? null) >
                <label class="form-check-label" for="padrao">
                  PADRÃO
                </label>
              </div>
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
  