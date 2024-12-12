@props([
  'pessoa' => null,
  'endereco' => null
])

<div class="col-12">
  <label for="info" class="form-label">Nome do endereço</label>
  <input type="text" class="form-control" name="info" 
    value="{{ old('info') ?? $endereco->info ?? null }}" placeholder="Ex. Endereço de Cobrança, Endereço de Entrega">
    @error('info') <div class="text-warning">{{ $message }}</div> @enderror

</div>

<div class="col-5 col-sm-4">
  <label for="cep" class="form-label">CEP<small class="text-danger-emphasis opacity-75"> * </small></label>
  <input type="text" class="form-control input-cep" name="cep"
    value="{{ old('cep') ?? $endereco->cep ?? null }}" required>
    @error('cep') <div class="text-warning">{{ $message }}</div> @enderror
</div>

<div class="col-12 col-sm-8">
  <label for="endereco" class="form-label">Endereço<small class="text-danger-emphasis opacity-75"> * </small></label>
  <input type="text" class="form-control" name="endereco" id="endereco" 
    value="{{ old('endereco') ?? $endereco->endereco ?? null }}" placeholder="Ex. Av. Brasil, 1234" required>
    @error('endereco') <div class="text-warning">{{ $message }}</div> @enderror
</div>

<div class="col-12 col-sm-6">
  <label for="complemento" class="form-label">Complemento</label>
  <input type="text" class="form-control" name="complemento" id="complemento" 
    value="{{ old('complemento') ?? $endereco->complemento ?? null }}" placeholder="Ex. Sala 101">
</div>

<div class="col-12 col-sm-6">
  <label for="bairro" class="form-label">Bairro</label>
  <input type="text" class="form-control" name="bairro" id="bairro" 
    value="{{ old('bairro') ?? $endereco->bairro ?? null }}">
</div>

<div class="col-12 col-sm-6">
  <label for="cidade" class="form-label">Cidade<small class="text-danger-emphasis opacity-75"> * </small></label>
  <input type="text" class="form-control" name="cidade" id="cidade" 
    value="{{ old('cidade') ?? $endereco->cidade ?? null }}" required>
    @error('cidade') <div class="text-warning">{{ $message }}</div> @enderror
</div>

<div class="col-4 col-sm-2">
  <label for="uf" class="form-label">Estado<small class="text-danger-emphasis opacity-75"> * </small></label>
  <input type="text" class="form-control" name="uf" id="uf" 
    value="{{ old('uf') ?? $endereco->uf ?? null }}" maxlength="2" pattern="[A-Z]{2}" 
    title="Duas letras maiúsculo" required>
    @error('uf') <div class="text-warning">{{ $message }}</div> @enderror
</div>
<div class="col-6 col-sm-4">
  <label for=""> &nbsp; </label>
  <div class="form-check mt-2 bg-light rounded" style="padding: 0.65rem 1.8rem 0.65rem;">
    <input class="form-check-input" name="end_padrao" value="1" id="end_padrao" type="checkbox"
    @checked(isset($pessoa->end_padrao, $endereco->id) && $pessoa->end_padrao == $endereco->id) >
    <label class="form-check-label" for="end_padrao">
      Endereço Padrão
    </label>
  </div>
</div>
