{{-- modal --}}
<div class="modal fade" id="{{ isset($material) ? 'materialModal'.$material->uid : 'materialModal'}}" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="materialModalLabel">Adicionar Material</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ isset($material) ? route('materiais-padroes-update', $material->uid) : route('materiais-padroes-store') }}">
          @csrf
          <div class="row gy-3">

            <div class="col-12">
              <label for="descricao" class="form-label">Descrição</label>
              <input type="text" class="form-control" id="descricao" name="descricao"
              value="{{ old('descricao') ?? $material->descricao ?? null }}">
              @error('descricao') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <label for="fabricante" class="form-label">Fabricante</label>
              <input type="text" class="form-control" id="fabricante" name="fabricante"
              value="{{ old('fabricante') ?? $material->fabricante ?? null }}">
              @error('fabricante') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-3">
              <label for="cod_fabricante" class="form-label">Código de Fabricante</label>
              <input type="text" class="form-control" id="cod_fabricante" name="cod_fabricante"
              value="{{ old('cod_fabricante') ?? $material->cod_fabricante ?? null }}">
              @error('cod_fabricante') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-9">
              <label for="fornecedor" class="form-label">Fornecedor</label>
              <input type="text" class="form-control" id="fornecedor" name="fornecedor"
              value="{{ old('fornecedor') ?? $material->fornecedor ?? null }}">
              @error('fornecedor') <div class="text-warning">{{ $message }}</div> @enderror
            </div>
            
            <div class="col-9">
              <label for="marca" class="form-label">Marca</label>
              <input type="text" class="form-control" id="marca" name="marca"
              value="{{ old('marca') ?? $material->marca ?? null }}">
              @error('marca') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-3">
              <label for="tipo" class="form-label">Tipo de Material</label>
              <select class="form-control" id="tipo" name="tipo">
                <option value="CURSOS">Cursos</option>
                <option value="INTERLAB">Interlab</option>
                <option value="AMBOS">Ambos</option>
              </select>
              @error('tipo') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-4">
              <label for="valor" class="form-label">Valor</label>
              <input type="text" class="form-control money" id="valor" name="valor"
              value="{{ old('valor') ?? $material->valor ?? null }}">
              @error('valor') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-4">
              <label for="tipo_despesa" class="form-label">Tipo de Despesa</label>
              <select class="form-control" id="tipo_despesa" name="tipo_despesa">
                <option value="FIXO">Fixo</option>
                <option value="VARIAVEL">Variável</option>
                <option value="OUTROS">Outros</option>
              </select>
              @error('tipo_despesa') <div class="text-warning">{{ $message }}</div> @enderror
            </div>

            <div class="col-4">
              <label class="form-label">&nbsp;</label>
              <div class="form-check mb-3 bg-light rounded" style="padding: 0.65rem 1.8rem 0.65rem;">
                <input class="form-check-input" id="padrao" name="padrao" value="1" value="1" type="checkbox"
                @checked(old('padrao') ?? ($material->padrao) ?? null) >
                <label class="form-check-label" for="padrao">
                  PADRÃO
                </label>
              </div>
            </div>

            <div class="col-12">
              <label for="observacoes" class="form-label">Observações</label>
              <textarea class="form-control" id="observacoes" name="observacoes">{{ old('observacoes') ?? $material->observacoes ?? null }}</textarea>
              @error('observacoes') <div class="text-warning">{{ $message }}</div> @enderror
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
  