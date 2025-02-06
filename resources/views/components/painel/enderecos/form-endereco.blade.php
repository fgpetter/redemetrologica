@props([
  'endereco' => null,
  'nome' => true,
  'padrao' => true,
  'required' => true,
])

@if($nome)
  <div class="col-12">
    <x-forms.input-field :value="old('info') ?? $endereco->info ?? ''" name="info"
      label="Nome do endereço" placeholder="Ex. Filial Caxias"/>
    @error('info') <div class="text-warning">{{ $message }}</div> @enderror
  </div>
@endif

<div class="col-5 col-sm-4">
  <x-forms.input-field :value="old('cep') ?? $endereco->cep ?? ''" name="cep"
    label="CEP" class="cep" :required="$required"/>
  @error('cep') <div class="text-warning">{{ $message }}</div> @enderror
</div>

<div class="col-12 col-sm-8">
  <x-forms.input-field :value="old('endereco') ?? $endereco->endereco ?? ''" name="endereco"
    label="Endereço com número" placeholder="Ex. Av. Brasil, 1234" :required="$required" />
  @error('endereco') <div class="text-warning">{{ $message }}</div> @enderror
</div>

<div class="col-12 col-sm-6">
  <x-forms.input-field :value="old('complemento') ?? $endereco->complemento ?? ''" name="complemento"
    label="Complemento" placeholder="Ex. Sala 101" />
  @error('complemento') <div class="text-warning">{{ $message }}</div> @enderror
</div>

<div class="col-12 col-sm-6">
  <x-forms.input-field :value="old('bairro') ?? $endereco->bairro ?? ''" name="bairro"
    label="Bairro" />
  @error('bairro') <div class="text-warning">{{ $message }}</div> @enderror
</div>

<div class="col-12 col-sm-6">
  <x-forms.input-field :value="old('cidade') ?? $endereco->cidade ?? ''" name="cidade"
    label="Cidade" :required="$required" />
  @error('cidade') <div class="text-warning">{{ $message }}</div> @enderror
</div>

<div class="col-4 col-sm-2">
  <label for="uf" class="form-label mb-0">UF<small class="text-danger-emphasis opacity-75"> * </small></label>
  <input type="text" class="form-control" name="uf" id="uf" 
    value="{{ old('uf') ?? $endereco->uf ?? null }}" maxlength="2" pattern="[A-Z]{2}" 
    title="Duas letras maiúsculo" :required="$required"
    oninput="this.value = this.value.toUpperCase()">
    @error('uf') <div class="text-warning">{{ $message }}</div> @enderror
</div>

@if($padrao)
  <div class="col-6 col-sm-4">
    <x-forms.input-check-pill name='end_padrao' label='Endereço Padrão'/>
  </div>
@endif

<script>

  document.addEventListener('DOMContentLoaded', function() {
    var addressFields = $('input[name="endereco"], input[name="bairro"], input[name="cidade"], input[name="uf"],input[name="complemento"]');

    $('input[name="cep"]').keyup(function() {
      const cep = $(this).val().replace(/\D/g, '');
      
      if (cep.length == 8) {
        addressFields.prop('disabled', true);
        $.ajax({
          url: '/painel/endereco/check',
          type: "GET", 
          dataType: 'json',
          data: { cep: cep },
          success: function(response){

            if(response.error){
              $.ajax({
                url: "https://viacep.com.br/ws/"+cep+"/json/",
                success: function(viacepResponse){
                  $('input[name="endereco"]').val(viacepResponse.logradouro);
                  $('input[name="bairro"]').val(viacepResponse.bairro);
                  $('input[name="cidade"]').val(viacepResponse.localidade);
                  $('input[name="uf"]').val(viacepResponse.uf);
                  addressFields.prop('disabled', false);
                }
              });

            } else {
              $('input[name="endereco"]').val(response.endereco);
              $('input[name="bairro"]').val(response.bairro);
              $('input[name="cidade"]').val(response.cidade); 
              $('input[name="uf"]').val(response.uf);
              addressFields.prop('disabled', false);
            }
          },
          error: function(data) {
            console.log('error!', data);
            addressFields.prop('disabled', false);
          }
        });
      }
    });
  });
</script>
