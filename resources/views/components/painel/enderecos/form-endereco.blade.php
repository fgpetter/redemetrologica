<div class="col-4">
  <label for="cep" class="form-label">CEP<small class="text-danger-emphasis opacity-75"> (Obrigatório) </small></label>
  <input type="text" class="form-control input-cep" name="cep"
    value="{{ old('cep') ?? $endereco->cep ?? null }}" required>
</div>

<div class="col-8">
  <label for="endereco" class="form-label">Endereço<small class="text-danger-emphasis opacity-75"> (Obrigatório) </small></label>
  <input type="text" class="form-control" name="endereco" id="endereco" 
    value="{{ old('endereco') ?? $endereco->endereco ?? null }}" placeholder="Ex. Av. Brasil, 1234" required>
</div>

<div class="col-6">
  <label for="complemento" class="form-label">Complemento</label>
  <input type="text" class="form-control" name="complemento" id="complemento" 
    value="{{ old('complemento') ?? $endereco->complemento ?? null }}" placeholder="Ex. Sala 101">
</div>

<div class="col-6">
  <label for="bairro" class="form-label">Bairro</label>
  <input type="text" class="form-control" name="bairro" id="bairro" 
    value="{{ old('bairro') ?? $endereco->bairro ?? null }}">
</div>

<div class="col-6">
  <label for="cidade" class="form-label">Cidade<small class="text-danger-emphasis opacity-75"> (Obrigatório) </small></label>
  <input type="text" class="form-control" name="cidade" id="cidade" 
    value="{{ old('cidade') ?? $endereco->cidade ?? null }}" required>
</div>

<div class="col-2">
  <label for="uf" class="form-label">Estado<small class="text-danger-emphasis opacity-75"> * </small></label>
  <input type="text" class="form-control" name="uf" id="uf" 
    value="{{ old('uf') ?? $endereco->uf ?? null }}" maxlength="2" pattern="[A-Z]{2}" title="Duas letras maiúsculo" required>
</div>
