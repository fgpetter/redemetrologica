<div class="col-4">
  <label for="cep" class="form-label">CEP</label>
  <input type="text" class="form-control" name="cep" id="input-cep" 
    value="{{ old('cep') ?? $endereco->cep ?? null }}">
  @error('cep') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-8">
  <label for="endereco" class="form-label">Endereço</label>
  <input type="text" class="form-control" name="endereco" id="endereco" 
    value="{{ old('endereco') ?? $endereco->endereco ?? null }}" placeholder="Ex. Av. Brasil, 1234">
  @error('endereco') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-6">
  <label for="complemento" class="form-label">Complemento</label>
  <input type="text" class="form-control" name="complemento" id="complemento" 
    value="{{ old('complemento') ?? $endereco->complemento ?? null }}" placeholder="Ex. Sala 101">
  @error('complemento') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-6">
  <label for="bairro" class="form-label">Bairro</label>
  <input type="text" class="form-control" name="bairro" id="bairro" 
    value="{{ old('bairro') ?? $endereco->bairro ?? null }}">
  @error('bairro') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-6">
  <label for="cidade" class="form-label">Cidade</label>
  <input type="text" class="form-control" name="cidade" id="cidade" 
    value="{{ old('cidade') ?? $endereco->cidade ?? null }}">
  @error('cidade') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-2">
  <label for="uf" class="form-label">Estado</label>
  <input type="text" class="form-control" name="uf" id="uf" 
    value="{{ old('uf') ?? $endereco->uf ?? null }}" maxlength="2" pattern="[A-Z]{2}" title="Duas letras maiúsculo">
  @error('uf') <div class="text-warning">{{ $message }}</div> @enderror 
</div>
