<div class="col-4">
  <label for="cep" class="form-label">CEP</label>
  <input type="text" class="form-control" name="cep" id="cep" 
    value="{{ old('cep') ?? null }}">
  @error('cep') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-8">
  <label for="logradouro" class="form-label">Logradouro</label>
  <input type="text" class="form-control" name="logradouro" id="logradouro" 
    value="{{ old('logradouro') ?? null }}" placeholder="Ex. Av. Brasil">
  @error('logradouro') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-6">
  <label for="numero" class="form-label">Número</label>
  <input type="text" class="form-control" name="numero" id="numero" 
    value="{{ old('numero') ?? null }}">
  @error('numero') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-6">
  <label for="complemento" class="form-label">Complemento</label>
  <input type="text" class="form-control" name="complemento" id="complemento" 
    value="{{ old('complemento') ?? null }}">
  @error('complemento') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-5">
  <label for="bairro" class="form-label">Bairro</label>
  <input type="text" class="form-control" name="bairro" id="bairro" 
    value="{{ old('bairro') ?? null }}">
  @error('bairro') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-5">
  <label for="cidade" class="form-label">Cidade</label>
  <input type="text" class="form-control" name="cidade" id="cidade" 
    value="{{ old('cidade') ?? null }}">
  @error('cidade') <div class="text-warning">{{ $message }}</div> @enderror 
</div>

<div class="col-2">
  <label for="uf" class="form-label">Estado</label>
  <input type="text" class="form-control" name="uf" id="uf" 
    value="{{ old('uf') ?? null }}">
  @error('uf') <div class="text-warning">{{ $message }}</div> @enderror 
</div>
